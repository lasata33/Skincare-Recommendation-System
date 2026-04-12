<?php
class Helper {
    public static function getGreeting() {
        date_default_timezone_set('Asia/Kathmandu');
        $hour = date('G');
        if ($hour < 12) return "Good morning";
        if ($hour < 18) return "Good afternoon";
        return "Good evening";
    }

    public static function getSkinTip($skin_type) {
        switch ($skin_type) {
            case 'Dry': return 'Moisturize twice daily and avoid hot showers.';
            case 'Oily': return 'Use oil-free products and cleanse twice a day.';
            case 'Combination': return 'Balance your routine with both hydration and oil control.';
            case 'Sensitive': return 'Avoid harsh exfoliants and choose gentle products.';
            case 'Normal': return 'Maintain with a balanced skincare routine.';
            default: return 'Take the quiz to get personalized tips!';
        }
    }
}
?>
