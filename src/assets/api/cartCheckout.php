<?php
    require "init.php";
    // header("Access-Control-Allow-Origin: *");
    $id = $_POST['id'] ;
    $data = array();
    // $count = 0;
    $sql_query = "SELECT * FROM cart where id = id ";
    $result = mysqli_query($con2, $sql_query);
    while($row=mysqli_fetch_assoc($result)){
        $data=array(
        'id'=>$row["id"],
        'prodId'=>$row["prodid"],
        'quantity'=>$row["quantity"],
        'variantsChosen'=>$row["variants_chosen"],
        'giftAddress'=>$row["gift_address"],
        'giftNote'=>$row["gift_note"],
        'totalPrice'=>$row["total_price"],
        'discount'=>$row["discount"],
        'variantPrice'=>$row["variant_price"],
        'hasImage'=>$row["has_image"],
        'deliveryDate'=>$row["delivery_date"],
        'requireDeliveryDate'=>$row["require_delivery_date"],
        'isOrdered'=>$row["is_ordered"],
        'userId'=>$row["user_id"],
        'giftTitle'=>$row["gift_title"],
        'giftOption'=>$row["gift_option"]);
    }

    mysqli_close($con1);
    mysqli_close($con2);
    echo json_encode($data);
        //echo "hello";
    
?>