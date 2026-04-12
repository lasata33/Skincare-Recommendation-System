import re
def analyze_ingredients(checked_data, user_skin_type):
    suitable, not_suitable, unknown = 0, 0, 0

    for item in checked_data:
        dataset_skin_type = str(item.get('skin_type', '')).lower().strip()

        if not dataset_skin_type:
            unknown += 1
            continue

        # Tokenize skin types like "Dry, Sensitive"
        tokens = [t.strip() for t in re.split(r'[;,/]', dataset_skin_type) if t.strip()]
        tokens = [t.lower() for t in tokens]

        if any('all' in t for t in tokens):
            suitable += 1
        elif user_skin_type.lower() in tokens:
            suitable += 1
        else:
            not_suitable += 1

    total = suitable + not_suitable + unknown
    score = round((suitable / max(1, total)) * 100, 1)

    if score >= 80:
        recommendation = "✅ Great match for your skin type!"
    elif score >= 50:
        recommendation = "⚠️ Some ingredients may not be ideal."
    else:
        recommendation = "❌ Not suitable for your skin type."

    return {
        "suitable": suitable,
        "not_suitable": not_suitable,
        "unknown": unknown,
        "score": score,
        "recommendation": recommendation
    }

if __name__ == "__main__":
    # Example test data
    checked_data = [
        {"ingredient": "Tocopherol", "skin_type": "All skin types"},
        {"ingredient": "Benzyl", "skin_type": "Sensitive skin"},
        {"ingredient": "Unknown", "skin_type": ""}
    ]
    user_skin_type = "dry"

    result = analyze_ingredients(checked_data, user_skin_type)
    print(result)
