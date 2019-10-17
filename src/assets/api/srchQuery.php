<?php

    require "init.php";

    $searchQ = $_POST["key"];
    $pincode = $_POST["pincode"];
    
    $filterSet = json_decode($_POST["filters"]);
    $sortSet = json_decode($_POST["sort"]);

    $tagNum = $_POST["tagNum"];
    $pageNum = $_POST["pageNum"];
    $offset = ($pageNum - 1)*$tagNum;

    $data = array();
    $count = 0;
    $request = "failed";

    $productQuery = "SELECT p.category_id, c.parentid, p.prodid, p.created_date, p.sold_count, p.avg_prcessing_time, p.avg_confrmn_time,p.shop_id,sl.seller_name, p.name, p.short_desc, p.base_price, (SELECT o.percentage from roninaks_smapr.offer o WHERE o.id = p.offer_id) as percentage, p.active_status, p.has_rfq, p.rating, p.has_order_confmn, p.has_instant_buy
    FROM roninaks_smapr.product p, roninaks_smapr.category c, roninaks_smausr.seller sl, roninaks_smausr.shop_details sh, roninaks_smausr.shipping_location_shop sls, roninaks_smapr.prod_shipping_price psp
    WHERE p.category_id = c.category_id AND p.shop_id = sh.id and sh.seller_id = sl.id and psp.prodid = p.prodid AND psp.shipping_location LIKE '%$pincode%' ";

    $locationQuery = " AND p.shipping_location_id = sls.id and sls.pincode LIKE '%$pincode%' ";
    
    $orderQuery = " ORDER by POSITION('$searchQ' IN p.name) ";

    $paginationQuery = " limit $tagNum offset $offset ";

    //Filter madness starts here
    $price = "";
    $rating = "";
    $freeShip = "";
    $hasRfq = "";
    $orderConfirm = "";
    $instantBuy = "";
    $delivery = "";
    $sortCondition = "";

        if(count($filterSet->priceRange)>0){
            // $price = " base_price => $filterSet->priceRange[0] and base_price <=$filterSet->priceRange[1] ";
        }
        if($filterSet->rating != 0){
            $rating = " and p.rating = $filterSet->rating ";
        }
        if($filterSet->freeShipping){
            $freeShip = " ";
        }
        if($filterSet->rfq){
            $hasRfq = " and p.has_rfq = 1 ";
        }
        if($filterSet->orderConfirm){
            $orderConfirm = " and p.has_order_confmn = 1 ";
        }
        if($filterSet->instantBuy){
            $instantBuy = " and p.has_instant_buy = 1 ";
        }
        if($filterSet->delivery){
            $delivery = " and p.shipping_option like '%$filterSet->delivery%' ";
        }
        if($sortSet->priceLH){
            $sortCondition = ", p.base_price asc ";
        } else if($sortSet->priceHL){
            $sortCondition = ", p.base_price desc ";
        } else if($sortSet->latest){
            $sortCondition = ", p.created_date desc ";
        } else if($sortSet->popular){
            $sortCondition = ", p.sold_count desc";
        } else if($sortSet->processLH){
            $sortCondition = ", p.avg_prcessing_time asc";
        } else if($sortSet->processHL){
            $sortCondition = ", p.avg_prcessing_time desc";
        } else if($sortSet->shipLH){
            $sortCondition = ", psp.price asc ";
        } else if($sortSet->shipHL){
            $sortCondition = ", psp.price desc ";
        } 

//Filter madness ends here

    // $sql = $productQuery . $locationQuery . $price . $rating . $freeShip . $hasRfq . $orderConfirm . $instantBuy . $delivery . $sortCondition . $paginationQuery;    
    $sql = $productQuery . " AND p.name LIKE '%$searchQ%' " . $locationQuery . $orderQuery . $price . $rating . $freeShip . $hasRfq . $orderConfirm . $instantBuy . $delivery . $sortCondition . $paginationQuery;
    
    $result = mysqli_query($con1,$sql);

    if(mysqli_num_rows($result) == 0){

        $categoryQuery = " AND p.category_id IN (select c.category_id from roninaks_smapr.category c where c.category LIKE '%$searchQ%') ";

        $orderQuery = " ORDER by POSITION('$searchQ' IN c.category) ";

        $sql = $productQuery . $categoryQuery . $locationQuery . $orderQuery . $price . $rating . $freeShip . $hasRfq . $orderConfirm . $instantBuy . $delivery . $sortCondition . $paginationQuery;

        $result = mysqli_query($con1,$sql);

        if(mysqli_num_rows($result) == 0){

            $ShopQuery = " AND p.shop_id IN (select sd.id from roninaks_smausr.shop_details sd where sd.shopname LIKE '%$searchQ%') ";
            
            $orderQuery = " ORDER by POSITION('$searchQ' IN sh.shopname) ";
            
            $sql = $productQuery . $ShopQuery . $locationQuery . $orderQuery . $price . $rating . $freeShip . $hasRfq . $orderConfirm . $instantBuy . $delivery . $sortCondition . $paginationQuery;

            $result = mysqli_query($con2,$sql);
            
            if(mysqli_num_rows($result) == 0){

                $sellerQuery = " AND p.shop_id IN (SELECT sd.id FROM roninaks_smausr.shop_details sd, roninaks_smausr.seller sl where sd.seller_id = sl.id and sl.seller_name LIKE '%$searchQ%') ";
                
                $orderQuery = " ORDER BY POSITION('$searchQ' IN sl.seller_name) ";

                $sql = $productQuery . $sellerQuery . $locationQuery . $orderQuery . $price . $rating . $freeShip . $hasRfq . $orderConfirm . $instantBuy . $delivery . $sortCondition . $paginationQuery;
    
                $result = mysqli_query($con1,$sql);

                if(mysqli_num_rows($result) == 0){
                   
                    $request = "not_found";
                
                }
            }
        }
    }
    
    $price = array();
    $prodId = array();
    $catId =  array();

    if(mysqli_num_rows($result) > 0 ){
            while( $row = mysqli_fetch_array($result) ){

                $request = "success";
                
                $price[$count] = $row["base_price"];
                
                $prodId[$count] = $row["prodid"];

                $catId[$count] = $row["category_id"];

                $data[$count++] = array(
                    "prodId"=>$row["prodid"],
                    "shopId"=>$row["shop_id"],
                    "sellerName"=>$row["seller_name"],
                    "prodName"=>$row["name"],
                    "desc"=>$row["short_desc"],
                    "basePrice"=>$row["base_price"],
                    "offerPercent"=>$row["percentage"],
                    "hasRfq"=>$row["has_rfq"],
                    "activeStatus"=>$row["active_status"],
                    "rating"=>$row["rating"],
                    "parentId"=>$row["parentid"],
                    "orderConfirm"=>$row["has_order_confmn"],
                    "instantBuy"=>$row["has_instant_buy"]
                );

            }
            $pageCount = ceil($count /$tagNum);

            //get sub categories
            $sql_subCategory = "SELECT category,category_id FROM category WHERE parentid IN (".implode(',',$catId).")";
            $result_subCategory = mysqli_query($con1,$sql_subCategory);

            $subCategory = array();
            $catCount = 0;
            
            while( $row = mysqli_fetch_array($result_subCategory) ){

                $subCategory[$catCount++] = array(
                    "category"=>$row["category"],
                    "categoryId"=>$row["category_id"]
                );
            
            }

            //get variants
            $sql_variants = "SELECT name FROM variant_info where prodid IN (".implode(',',$prodId).") group by name HAVING COUNT(name) > 5";
            $result_variants = mysqli_query($con1,$sql_variants);

            $variants = array();
            $varCount = 0;

            while( $row = mysqli_fetch_array($result_variants) ){

                $variants[$varCount++] = $row["name"];
            
            }
        //get shipping options
        $sql_ship = "SELECT shipping_option FROM product where category_id IN (".implode(',',$catId).") GROUP BY shipping_option";
        $result_ship = mysqli_query($con1,$sql_ship);

        $shipMethods = array();
        $shipCount = 0;

        while( $row = mysqli_fetch_array($result_ship) ){

            $shipMethods[$shipCount++] = $row["shipping_option"];

        }
        // echo $sql;
        echo json_encode(
            array(
                "request"=>$request,
                "data"=>$data,
                "pageCount"=>$pageCount,
                "subCategory"=>$subCategory,
                "priceRange"=>$price,
                "variants"=>$variants,
                "shipMethods"=>$shipMethods
            )
        );
    } else {
        echo json_encode(
            array(
                "request"=>$request
            )
        );
    }


?>