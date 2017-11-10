<?php

/**
 * Plugin Name: fWP-GameIntegration
 * Description: WooCommerce integration with game database to auto activate the player purchase
 * Author: Lucas Formiga
 * Author URI: https://github.com/LucasFormiga
 * Version: 1.4
 *
 * @package WooCommerce_fWP
 */

defined( 'ABSPATH' ) or die( 'Access Denied' );

const DB_Driver = "";
const DB_Hostname = "";
const DB_Port = "";
const DB_Name = "";
const DB_Username = "";
const DB_Password = "";


/**
 * !! IMPORTANT !!
 * Remember to change the database, table and param names...
 * Also, remember to change the product ID too.
 * Everything that are here is only for test purpose.
 */

function is_registered( $email ) {

  $sql = new PDO( DB_Driver . ':host=' . DB_Hostname . ';port=' . DB_Port .';dbname=' . DB_Name, DB_Username, DB_Password );

  $query = "SELECT player_email FROM dw_balance WHERE player_email = :email"; // You have to change this query to use your game panel or game database and verify if it has the user registered
  $res = $sql->prepare( $query );

  $res->bindValue(':email', $email, PDO::PARAM_STR);
  $res->execute();

  if ($res->rowCount() > 0):
    return true;
  else:
    return false;
  endif;

}

function register_customer( $email ) {

  $sql = new PDO( DB_Driver . ':host=' . DB_Hostname . ';port=' . DB_Port .';dbname=' . DB_Name, DB_Username, DB_Password );

  $query = ""; // You have to change this query to use your game panel or game database to register the user if it is not registered
  $set = $sql->prepare( $query );

  $set->bindValue(':email', $email, PDO::PARAM_STR);

  $set->execute();

}

function add_balance( $email, $quantity ) {

  $sql = new PDO( DB_Driver . ':host=' . DB_Hostname . ';port=' . DB_Port .';dbname=' . DB_Name, DB_Username, DB_Password );

  $query = "UPDATE dw_balance SET coins = :qty WHERE player_email = :email";
  $set = $sql->prepare( $query );

  $set->bindValue(':email', $email, PDO::PARAM_STR);
  $set->bindValue(':qty', $quantity, PDO::PARAM_INT);

  $set->execute();

}

function execute_when_completed( $order_id ) {

  $get_order = new WC_Order( $order_id );

  $items = $get_order->get_items();
  $email = $get_order->billing_email;

  $coins = 0;

  foreach ( $items as $item ):
    switch ( $item['product_id'] ):
      // Use the case with the product_id to get the product details
      case 157:
        $price = 1000 * $item['qty'];
        $coins += $price;
      break;
    endswitch;
  endforeach;

  if ( $coins > 0 ):
    if (is_registered( $email )):
      add_balance( $email, $coins );
    else:
      register_customer( $email );
      add_balance( $email, $coins );
    endif;
  endif;

}

add_action( 'woocommerce_order_status_completed' , 'execute_when_completed' );
