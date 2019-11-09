<?php

        require "init.php";

        $userId = $_POST["userId"];

        $tagNum = $_POST["tagNum"];
        $pageNum = $_POST["pageNum"];
        $offset = ($pageNum - 1)*$tagNum;

        $paginationQuery = " limit $tagNum offset $offset ";

        $sql = "SELECT sd.* FROM follow f, shop_details sd where f.userid = '$userId' and sd.id = f.shopid" . $paginationQuery;
        $result = mysqli_query($con2,$sql);

        $data = array();
        $count = 0;

        $request = "failed";
        
        if(mysqli_num_rows($result) > 0){
            while( $row = mysqli_fetch_array($result) ){
            
                $request = "success";
                $data[$count++] = array(
                "shopId"=>$row["id"],
                "sellerId"=>$row["seller_id"],
                "shopName"=>$row["shopname"],
                "bannerImg"=>$row["primary_image"],
                "shopImg1"=>$row["prod_image_1"],
                "shopImg2"=>$row["prod_image_2"],
                "shopImg3"=>$row["prod_image_3"],
                "shopRating"=>$row["rating"],
                "rfq"=>$row["rfq"],
                "shopDesc"=>$row["description"]
                );
            }
            $pageCount = ceil($count /$tagNum);
    
            echo json_encode(array("request"=>$request,"data"=>$data,"pageCount"=>$pageCount));
        } else {
            echo json_encode(array("request"=>$request));
        }
        
?>