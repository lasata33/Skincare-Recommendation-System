<?php
class WeatherService {
    private $db;
    private $apiKey = "b04089b2809664f4dad49424ed3fedf9";

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getWeather($city) {
        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $this->apiKey . "&units=metric";
        $response = file_get_contents($url);

        if (!$response) return null;

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['main'])) return null;

        return [
            'temp' => $data['main']['temp'],
            'humidity' => $data['main']['humidity'],
            'condition' => $data['weather'][0]['main']
        ];
    }

    public function simplifyCondition($temp, $humidity, $condition) {
        if ($temp > 30) return "hot";
        if ($temp < 15) return "cold";
        if ($humidity > 70) return "humid";
        if (strtolower($condition) === "rain") return "rainy";
        return "mild";
    }

    public function getTip($skinType, $weatherCondition) {
        $stmt = $this->db->prepare("SELECT recommendation FROM recommendations WHERE skin_type = ? AND weather_condition = ?");
        $stmt->bind_param("ss", $skinType, $weatherCondition);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result['recommendation'] ?? "Mild weather — enjoy your regular skincare routine. Keep glowing!";
    }
}
?>
