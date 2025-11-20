<?php
require_once "config/config.php";
require_once "includes/functions.php";

// Get all search parameters
$keyword = $_GET["keyword"] ?? "";
$province = $_GET["province"] ?? "";
$priceRange = $_GET["price_range"] ?? "";
$nearTransit = $_GET["near_transit"] ?? "";
$distance = $_GET["distance"] ?? "";
$roomType = $_GET["room_type"] ?? "";
$amenities = $_GET["amenities"] ?? [];
$sort = $_GET["sort"] ?? "relevance";

// Legacy support for max_price parameter
$maxPrice = $_GET["max_price"] ?? null;

// Parse price range if provided
$minPrice = null;
$maxPriceFromRange = null;

if (!empty($priceRange)) {
    $priceParts = explode("-", $priceRange);
    if (count($priceParts) == 2) {
        $minPrice = intval($priceParts[0]);
        $maxPriceFromRange = intval($priceParts[1]);
    }
}

// Build query parameters for rooms.php
$queryParams = [];

// Add keyword if provided
if (!empty($keyword)) {
    $queryParams["keyword"] = $keyword;
}

// Add province if provided
if (!empty($province)) {
    $queryParams["province"] = $province;
}

// Add price range parameters
if ($minPrice !== null) {
    $queryParams["min_price"] = $minPrice;
}
if ($maxPriceFromRange !== null) {
    $queryParams["max_price"] = $maxPriceFromRange;
} elseif ($maxPrice !== null) {
    // Legacy support
    $queryParams["max_price"] = $maxPrice;
}

// Add transit filter
if (!empty($nearTransit)) {
    $queryParams["near_transit"] = $nearTransit;
}

// Add distance filter
if (!empty($distance)) {
    $queryParams["distance"] = $distance;
}

// Add room type filter
if (!empty($roomType)) {
    $queryParams["room_type"] = $roomType;
}

// Add amenities filter
if (!empty($amenities) && is_array($amenities)) {
    $queryParams["amenities"] = implode(",", $amenities);
}

// Add sort parameter
if (!empty($sort) && $sort !== "relevance") {
    $queryParams["sort"] = $sort;
}

// Build query string
$queryString = http_build_query($queryParams);

// Redirect to rooms.php with all search parameters
if (!empty($queryString)) {
    redirect("rooms.php?" . $queryString);
} else {
    // No search parameters provided, redirect to all rooms
    redirect("rooms.php");
}
?>
