<?php
require_once __DIR__ . '/../config/config.php';

// ฟังก์ชันดึงข้อมูลห้องแนะนำ
function getFeaturedRooms($limit = 6) {
    if (!isDBConnected()) {
        return [];
    }
    $db = getDB();
    if (!$db) return [];
    
    try {
        $stmt = $db->prepare("SELECT r.*, bp.business_name 
                              FROM rooms r 
                              JOIN business_profiles bp ON r.business_id = bp.id 
                              WHERE r.status = 'approved' 
                              ORDER BY r.featured DESC, r.created_at DESC 
                              LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ฟังก์ชันดึงข้อมูลสินค้าแนะนำ
function getFeaturedProducts($limit = 6) {
    if (!isDBConnected()) {
        return [];
    }
    $db = getDB();
    if (!$db) return [];
    
    try {
        $stmt = $db->prepare("SELECT p.*, pc.name as category_name, bp.business_name 
                              FROM products p 
                              JOIN product_categories pc ON p.category_id = pc.id 
                              JOIN business_profiles bp ON p.business_id = bp.id 
                              WHERE p.status = 'approved' 
                              ORDER BY p.featured DESC, p.created_at DESC 
                              LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ฟังก์ชันดึงข้อมูลโฆษณา
function getActiveAdvertisements($type = 'banner') {
    if (!isDBConnected()) {
        return [];
    }
    $db = getDB();
    if (!$db) return [];
    
    try {
        $stmt = $db->prepare("SELECT * FROM advertisements 
                              WHERE ad_type = ? AND status = 'active' 
                              AND (start_date IS NULL OR start_date <= CURDATE()) 
                              AND (end_date IS NULL OR end_date >= CURDATE()) 
                              ORDER BY created_at DESC");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ฟังก์ชันดึงรูปภาพห้อง
function getRoomImages($roomId) {
    if (!isDBConnected()) {
        return [];
    }
    $db = getDB();
    if (!$db) return [];
    
    try {
        $stmt = $db->prepare("SELECT * FROM room_images WHERE room_id = ? ORDER BY is_primary DESC, id ASC");
        $stmt->execute([$roomId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ฟังก์ชันดึงข้อมูลห้องตาม ID
function getRoomById($roomId) {
    if (!isDBConnected()) {
        return null;
    }
    $db = getDB();
    if (!$db) return null;
    
    try {
        $stmt = $db->prepare("SELECT r.*, bp.business_name, bp.user_id as business_user_id 
                              FROM rooms r 
                              JOIN business_profiles bp ON r.business_id = bp.id 
                              WHERE r.id = ?");
        $stmt->execute([$roomId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// ฟังก์ชันดึงข้อมูลสินค้าตาม ID
function getProductById($productId) {
    if (!isDBConnected()) {
        return null;
    }
    $db = getDB();
    if (!$db) return null;
    
    try {
        $stmt = $db->prepare("SELECT p.*, pc.name as category_name, bp.business_name, bp.user_id as business_user_id 
                              FROM products p 
                              JOIN product_categories pc ON p.category_id = pc.id 
                              JOIN business_profiles bp ON p.business_id = bp.id 
                              WHERE p.id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// ฟังก์ชันค้นหาห้อง
function searchRooms($keyword = '', $province = '', $maxPrice = null, $minPrice = null, $latitude = null, $longitude = null, $radius = null) {
    if (!isDBConnected()) {
        return [];
    }
    $db = getDB();
    if (!$db) return [];
    
    try {
        $conditions = ["r.status = 'approved'"];
        $params = [];
        
        if (!empty($keyword)) {
            $conditions[] = "(r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        if (!empty($province)) {
            $conditions[] = "r.province = ?";
            $params[] = $province;
        }
        
        if ($maxPrice !== null) {
            $conditions[] = "r.price <= ?";
            $params[] = $maxPrice;
        }
        
        if ($minPrice !== null) {
            $conditions[] = "r.price >= ?";
            $params[] = $minPrice;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $query = "SELECT r.*, bp.business_name, 
                  (6371 * acos(cos(radians(?)) * cos(radians(r.latitude)) * cos(radians(r.longitude) - radians(?)) + sin(radians(?)) * sin(radians(r.latitude)))) AS distance
                  FROM rooms r 
                  JOIN business_profiles bp ON r.business_id = bp.id 
                  WHERE $whereClause";
        
        if ($latitude !== null && $longitude !== null && $radius !== null) {
            $query .= " HAVING distance <= ? ORDER BY distance ASC";
            array_unshift($params, $latitude, $longitude, $latitude);
            $params[] = $radius;
        } else {
            $query .= " ORDER BY r.created_at DESC";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ฟังก์ชันเพิ่มจำนวนผู้ชม
function incrementViews($table, $id) {
    if (!isDBConnected()) {
        return false;
    }
    $db = getDB();
    if (!$db) return false;
    
    try {
        $stmt = $db->prepare("UPDATE $table SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// ฟังก์ชันตรวจสอบ Wishlist
function isInWishlist($userId, $roomId = null, $productId = null) {
    if (!isDBConnected()) {
        return false;
    }
    $db = getDB();
    if (!$db) return false;
    
    try {
        if ($roomId) {
            $stmt = $db->prepare("SELECT id FROM wishlists WHERE user_id = ? AND room_id = ?");
            $stmt->execute([$userId, $roomId]);
        } else if ($productId) {
            $stmt = $db->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            return false;
        }
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// ฟังก์ชันเพิ่ม Wishlist
function addToWishlist($userId, $roomId = null, $productId = null) {
    if (!isDBConnected()) {
        return ['success' => false, 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'];
    }
    $db = getDB();
    if (!$db) {
        return ['success' => false, 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'];
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO wishlists (user_id, room_id, product_id) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $roomId, $productId]);
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// ฟังก์ชันลบ Wishlist
function removeFromWishlist($userId, $roomId = null, $productId = null) {
    if (!isDBConnected()) {
        return ['success' => false, 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'];
    }
    $db = getDB();
    if (!$db) {
        return ['success' => false, 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'];
    }
    
    try {
        if ($roomId) {
            $stmt = $db->prepare("DELETE FROM wishlists WHERE user_id = ? AND room_id = ?");
            $stmt->execute([$userId, $roomId]);
        } else if ($productId) {
            $stmt = $db->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        }
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// ฟังก์ชันดึงรีวิว
function getReviews($roomId = null, $productId = null, $limit = 10) {
    if (!isDBConnected()) {
        return [];
    }
    $db = getDB();
    if (!$db) return [];
    
    try {
        $conditions = ["r.status = 'approved'"];
        $params = [];
        
        if ($roomId) {
            $conditions[] = "r.room_id = ?";
            $params[] = $roomId;
        } else if ($productId) {
            $conditions[] = "r.product_id = ?";
            $params[] = $productId;
        }
        
        $whereClause = implode(' AND ', $conditions);
        $query = "SELECT r.*, u.full_name, u.username 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE $whereClause 
                  ORDER BY r.created_at DESC 
                  LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ฟังก์ชันคำนวณคะแนนเฉลี่ย
function getAverageRating($roomId = null, $productId = null) {
    if (!isDBConnected()) {
        return ['avg_rating' => 0, 'total_reviews' => 0];
    }
    $db = getDB();
    if (!$db) {
        return ['avg_rating' => 0, 'total_reviews' => 0];
    }
    
    try {
        if ($roomId) {
            $stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                                  FROM reviews 
                                  WHERE room_id = ? AND status = 'approved'");
            $stmt->execute([$roomId]);
        } else if ($productId) {
            $stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                                  FROM reviews 
                                  WHERE product_id = ? AND status = 'approved'");
            $stmt->execute([$productId]);
        } else {
            return ['avg_rating' => 0, 'total_reviews' => 0];
        }
        $result = $stmt->fetch();
        return $result ? $result : ['avg_rating' => 0, 'total_reviews' => 0];
    } catch (PDOException $e) {
        return ['avg_rating' => 0, 'total_reviews' => 0];
    }
}
?>

