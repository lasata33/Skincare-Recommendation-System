<?php
class IngredientAnalyzer {
    private $db;
    private $dataset = [];

    public function __construct($conn) {
        $this->db = $conn;
        $this->loadDataset();
    }

    private function loadDataset() {
        $path = __DIR__ . '/../user/ingredient_data_cleaned.csv'; // if it's in /user/
        if (($handle = fopen($path, "r")) !== FALSE) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== FALSE) {
                $this->dataset[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
    }

    public function extractIngredients($imagePath) {
        $ocrText = shell_exec("tesseract " . escapeshellarg($imagePath) . " stdout");
        $lines = preg_split("/[\r\n]+/", $ocrText);
        $ingredients = [];

        foreach ($lines as $line) {
            $parts = preg_split("/[,;]+/", $line);
            foreach ($parts as $p) {
                $clean = trim(preg_replace("/[^a-zA-Z0-9\s\-]/", "", $p));
                if (strlen($clean) > 3) $ingredients[] = $clean;
            }
        }

        return [$ocrText, $ingredients];
    }

    public function analyze($ingredients, $skinType) {
        $matched = [];
        $skinType = strtolower(trim($skinType));

        foreach ($ingredients as $ingredient) {
            foreach ($this->dataset as $data) {
                $ingredientClean = strtolower(trim($ingredient));
                $datasetIngredient = strtolower(trim($data['Ingredient']));
                $similarity = similar_text($ingredientClean, $datasetIngredient, $percent);

                if ($percent >= 70) {
                    $types = array_map(fn($t) => strtolower(trim($t)) === 'all skin types' ? 'all' : strtolower(trim($t)), explode(',', $data['Skin Type']));

                    $isSuitable = $skinType === 'combination'
                        ? in_array('dry', $types) || in_array('oily', $types) || in_array('all', $types)
                        : in_array($skinType, $types) || in_array('all', $types);

                    $matched[] = [
                        'ingredient' => $ingredient,
                        'type' => $data['Type'],
                        'purpose' => $data['Purpose'],
                        'skinType' => $data['Skin Type'],
                        'suitable' => $isSuitable,
                        'isAll' => in_array('all', $types)
                    ];
                    break;
                }
            }
        }

        return $matched;
    }

    public function calculateFeasibility($matched) {
        $total = count($matched);
        $score = 0;

        foreach ($matched as $m) {
            if ($m['suitable']) {
                $score += $m['isAll'] ? 0.5 : 1;
            }
        }

        return $total > 0 ? round(($score / $total) * 100) : 0;
    }

    public function saveResult($user_id, $ocrText, $skinType, $feasibilityPercent) {
        $productName = "Scanned Product";
        $stmt = $this->db->prepare("INSERT INTO ingredientanalysis (user_id, product_name, ingredients, skin_type, analysis_result, date_scanned) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssi", $user_id, $productName, $ocrText, $skinType, $feasibilityPercent);
        $stmt->execute();
        $stmt->close();
    }

    public function getSkinType($id) {
    $stmt = $this->db->prepare("SELECT skintype FROM users_db WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['skintype'] ?? '';
}

public function getScanHistory($user_id, $limit = 10) {
    $stmt = $this->db->prepare("SELECT product_name, analysis_result, date_scanned FROM ingredientanalysis WHERE user_id = ? ORDER BY date_scanned DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    $stmt->close();
    return $history;
}

public function getCombinedHistory($user_id, $limit = 20) {
    $history = [];
    
    // Get scan history
    $stmt = $this->db->prepare("SELECT 'scan' as type, product_name as title, analysis_result as score, date_scanned as date FROM ingredientanalysis WHERE user_id = ? ORDER BY date_scanned DESC LIMIT ?");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
    }
    
    // Get skin type history
    $stmt = $this->db->prepare("SELECT 'skintype' as type, CONCAT('Changed from ', old_skintype, ' to ', new_skintype) as title, NULL as score, changed_at as date FROM skintype_history WHERE user_id = ? ORDER BY changed_at DESC LIMIT ?");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
    }
    
    // Sort combined history by date (newest first)
    usort($history, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // Return only the limit number of items
    return array_slice($history, 0, $limit);
}


}
?>
