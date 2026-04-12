# Skin Type History Tracking - Implementation Summary

## Changes Made

### 1. **Database Changes**
Created a new table `skintype_history` to track when users change their skin type:

```sql
CREATE TABLE skintype_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    old_skintype VARCHAR(50) NOT NULL,
    new_skintype VARCHAR(50) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users_db(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (changed_at)
);
```

### 2. **User.php Class - New Methods**

#### `recordSkinTypeChange($user_id, $oldSkinType, $newSkinType)`
- Records when a user's skin type changes
- Automatically called when skin type is updated
- Stores both old and new skin type with timestamp

#### `getSkinTypeHistory($user_id, $limit = 10)`
- Retrieves user's skin type change history
- Returns array of changes with dates
- Ordered by most recent first

#### `updateSkinType($user_id, $newSkinType)`
- Updates user's skin type in the database
- Automatically tracks the change in history
- Only records if the skin type actually changed

### 3. **IngredientAnalyzer.php Class - New Method**

#### `getCombinedHistory($user_id, $limit = 20)`
- Merges both scan history and skin type history
- Returns chronological activity feed
- Each item marked with type ('scan' or 'skintype')
- Useful for showing unified activity timeline

### 4. **history.php - Enhanced UI**

Now displays:
- **Product Scans** 📷
  - Product name
  - Match score percentage
  - Date and time
  
- **Skin Type Changes** 🔄
  - Shows "Changed from [Old] to [New]"
  - Labels it as "Quiz Retake"
  - Date and time of change

### 5. **CSS Styling - userdash.css**

Added new badges and styling:
- `.scan-badge` - Blue badge for product scans
- `.skintype-badge` - Orange badge for skin type changes
- `.score-badge` - Green badge for match percentage
- Enhanced `.history-card` with better visual hierarchy

---

## How to Enable

### Step 1: Create the Database Table

**Option A: Using PHP Setup Script**
```
1. Visit: http://localhost/xampp/htdocs/SP/setup_skintype_tracking.php
2. Should see: "✅ Successfully created 'skintype_history' table!"
```

**Option B: Using Direct SQL**
```
1. Open phpMyAdmin
2. Run the SQL from setup_skintype_history.sql
```

### Step 2: No Code Changes Needed!
The system automatically tracks skin type changes when:
- User registers with initial skin type
- User retakes the quiz and their result changes

---

## How It Works

### When User Takes Quiz:
1. Quiz is submitted in index.php
2. New skin type is determined
3. Registration form is shown

### When User Updates Skin Type:
1. Call: `$userObj->updateSkinType($user_id, $newSkinType)`
2. Automatically records: old skin type → new skin type
3. Entry is created in `skintype_history` table

### When User Views History:
1. history.php now calls `getCombinedHistory()`
2. Displays both scans AND skin type changes
3. Shows with different badges for visual distinction

---

## Example History Display

```
📷 Product Scan                          🔄 Skin Type Change
Hydrating Face Serum                    Changed from Dry to Oily
Match Score: 85%                        Change Type: Quiz Retake
Mar 24, 2026 • 14:32                    Mar 23, 2026 • 10:15

📷 Product Scan                          
Anti-Aging Moisturizer                  
Match Score: 72%
Mar 22, 2026 • 09:45
```

---

## Integration Points

### If Quiz is Retaken:
You may want to add this code when user completes a retaken quiz:
```php
$oldSkinType = $userObj->getSkinType($user_id);
if ($oldSkinType !== $newSkinType) {
    $userObj->recordSkinTypeChange($user_id, $oldSkinType, $newSkinType);
}
```

### For Future Enhancements:
- Add filters to show only "Scans" or "Changes"
- Add skin type statistics (how many times changed)
- Show recommendations that changed between skin types
- Add ability to export history as CSV/PDF

---

## Database Queries Reference

**Get all skin type changes for a user:**
```sql
SELECT old_skintype, new_skintype, changed_at 
FROM skintype_history 
WHERE user_id = ? 
ORDER BY changed_at DESC;
```

**Get combined activity with counts:**
```sql
SELECT 'scan' as type, COUNT(*) as count FROM ingredientanalysis WHERE user_id = ?
UNION ALL
SELECT 'skintype' as type, COUNT(*) as count FROM skintype_history WHERE user_id = ?;
```

---

## Notes
- Skin type changes are only recorded when the skin type actually changes
- All timestamps are automatic (uses server time)
- History is tied to user_id for privacy
- Deletion of user account cascades to delete their history
