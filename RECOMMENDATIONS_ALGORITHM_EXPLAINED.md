📊 RECOMMENDATIONS SYSTEM - COMPLETE EXPLANATION
=================================================

🎯 HOW IT WORKS:
================

The recommendation system is built on a SCORING ALGORITHM that matches user profile with products based on 3 criteria:

1️⃣ SKIN TYPE MATCHING
   - User's skin type from quiz (Dry, Oily, Combination, Sensitive, Normal)
   - Matched against product's skintype field
   - Score += 1 if matches

2️⃣ CONCERN MATCHING
   - User's skin concern (acne, aging, sensitivity, etc.)
   - Matched against product's concern field
   - Score += 1 if matches

3️⃣ TAGS/INTERESTS MATCHING
   - User's tags are split by comma (e.g., "hydrating,anti-acne,natural")
   - Each tag is checked against product's tags field
   - Score += 1 for EACH tag that matches
   - (A product can score multiple points from different tags)

📐 SCORING SYSTEM:
==================

Maximum Score: 4+ points (1 skintype + 1 concern + 2+ tags)

Example 1 - High Match:
  User: Dry skin, concern: aging, tags: "hydrating,anti-wrinkle,natural"
  Product: Name: "Hyaluronic Acid Serum"
           Skintype: Dry
           Concern: Aging
           Tags: "hydrating,anti-wrinkle,luxury"
  
  Score = 1 (skintype match) + 1 (concern) + 1 (hydrating) + 1 (anti-wrinkle) = 4 points ⭐

Example 2 - Medium Match:
  User: Oily skin, concern: acne, tags: "oil-control"
  Product: Name: "Niacinamide Toner"
           Skintype: Oily
           Concern: acne
           Tags: "mattifying,acne-fighting"
  
  Score = 1 (skintype) + 1 (concern) = 2 points

Example 3 - Low/No Match:
  User: Sensitive skin, concern: dryness, tags: "gentle"
  Product: Name: "Strong Chemical Peel"
           Skintype: Oily
           Concern: acne
           Tags: "exfoliating,harsh"
  
  Score = 0 (no matches)

🔄 ALGORITHM FLOW:
==================

1. User takes quiz → Gets skin type determined
2. User data stored: skintype, concern, tags
3. When recommends.php loads:
   ├─ Fetch all products from database
   ├─ For each product:
   │  ├─ Check if skintype matches → +1
   │  ├─ Check if concern matches → +1
   │  └─ Check each tag → +1 per tag match
   ├─ Sort products by score (highest first)
   └─ Display top 3 as "Top Picks for You"

4. Also generates "Personalized Skincare Routine":
   ├─ Picks one product from each category:
   │  ├─ Cleanser
   │  ├─ Toner
   │  ├─ Serum
   │  ├─ Moisturizer
   │  └─ Sunscreen
   └─ Selects the highest-scoring product for each category

💻 CODE IMPLEMENTATION:
======================

Location: classes/Product.php → getRecommendations() method

```php
public function getRecommendations($skintype, $concern, $tags) {
    // 1. Get all products
    $result = $this->db->query("SELECT * FROM products ORDER BY id DESC");
    $products = [];
    $userTags = array_map('trim', explode(',', $tags));

    // 2. Score each product
    while ($row = $result->fetch_assoc()) {
        $score = 0;

        // Skin type match
        if (!empty($skintype) && stripos($row['skintype'], $skintype) !== false) {
            $score++;
        }

        // Concern match
        if (!empty($concern) && stripos($row['concern'], $concern) !== false) {
            $score++;
        }

        // Tag matches (can add multiple points)
        foreach ($userTags as $tag) {
            if (!empty($tag) && stripos($row['tags'], $tag) !== false) {
                $score++;
            }
        }

        $row['score'] = $score;
        $products[] = $row;
    }

    // 3. Sort by score (descending)
    usort($products, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $products;  // Highest scored products first
}
```

Key Points:
- Uses stripos() = case-insensitive matching
- 'strpos' looks for substring match (so "hydrating" matches "hydrating serum")
- Returns sorted array with score attached to each product
- Top 3 products are shown as "Top Picks"

🧖 PERSONALIZED ROUTINE:
========================

After showing top 3 picks, the system creates a 5-step routine:

1. CLEANSER    → Highest-scoring cleanser product
2. TONER       → Highest-scoring toner product
3. SERUM       → Highest-scoring serum product
4. MOISTURIZER → Highest-scoring moisturizer product
5. SUNSCREEN   → Highest-scoring sunscreen product

Each product shown with description and price.

📱 USER EXPERIENCE FLOW:
=======================

User Not Logged In:
└─ See message: "Take the quiz to unlock personalized recommendations"
└─ See ALL products (no filtering)

User Completed Basic Quiz:
└─ See: "You have [skintype] skin type"
└─ See: Prompt to take Intermediate quiz
└─ See: Top 3 matching products
└─ See: Personalized 5-step routine

User Completed All Quizzes:
└─ Unlock all retake options
└─ See: "🎉 You've completed all quiz blocks!"
└─ See: Top 3 matching products (most accurate)
└─ See: Detailed 5-step personalized routine

✅ STRENGTHS:
=============
✓ Simple but effective scoring
✓ Flexible - can match multiple criteria
✓ Fast - no database queries per product
✓ Scalable - works with any number of products
✓ Fair - each criterion weighted equally
✓ Personalized routine - practical skincare advice

⚠️ LIMITATIONS:
===============
✗ Equal weighting - skintype and one tag treated same
✗ No rating-based ranking (all 5-star products mixed)
✗ No popularity consideration
✗ Substring matching can cause false positives
✗ No seasonal recommendations
✗ No price-based filtering

🚀 POSSIBLE IMPROVEMENTS:
=========================

1. Weight scoring:
   skintype: +2 (more important)
   concern: +2
   tags: +1 (less important)

2. Add product rating/reviews to scoring

3. Consider budget/price range

4. Add seasonal adjustments

5. Track which products user actually bought/liked

6. Implement collaborative filtering (if user A likes X and user B likes X, recommend B to A)

7. Add "Might Also Like" based on product categories

8. Show match percentage (3/4 = 75% match)

═══════════════════════════════════════════════════════════════════
