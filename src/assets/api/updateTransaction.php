<?php
    require "init.php";
    $txnid = $_POST["txnid"];
    $orderId = $_POST["orderId"];
    $prodname = $_POST["prodname"];

    // $sql_query1="UPDATE `transaction_details` SET `txnid`=$txnid,`orderid`=$orderId,`status`='pending_conformation',`payment_mode`='cash',`prod_name`='$prodname' ";
    // $result1 = mysqli_query($con2, $sql_query1);
    $sql_query1="INSERT INTO `transaction_details`( `txnid`, `orderid`, `status`, `payment_mode`, `prod_name`) VALUES ('$txnid',$orderId,'pending_conformation','internet','$prodname')";
    $result1 = mysqli_query($con2, $sql_query1);

    $sql_query2="UPDATE `purchase_order` SET `cancellation_message`= null WHERE orderid = $orderId ";
    $result2 = mysqli_query($con2, $sql_query2);
    if($result2){
        $sql_query2="DELETE FROM `purchase_order` WHERE cancellation_message LIKE '%temp%'";
        $result2 = mysqli_query($con2, $sql_query2);
    }

    // $notifMsgArr = array("new order placed","customer_order",$coId,"purchase_order",$orderId);
    // $notifMsg = implode("!~!",$notifMsgArr);
    // $sql_notif="INSERT INTO `notification`(`userid`, `message`, `prodid`, `type`) VALUES ($userId,'$notifMsg ',$prodId,'new order')";
    // $result = mysqli_query($con2, $sql_notif); 

    if(!$result1)
    {
        $response ="Error";
    }
    else
    {
        $response ="Success";
    }
    mysqli_close($con1);
    mysqli_close($con2);
    echo json_encode($response);
?>