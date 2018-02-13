<?php
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

class Hendcorp_Processor extends WP_Background_Process {

  use Hendcorp_Data_Fetcher;
  /**
   * @var string
   */
  protected $action = 'hendcorp_process';
  /**
   * Task
   *
   * Override this method to perform any actions required on each
   * queue item. Return the modified item for further processing
   * in the next pass through. Or, return false to remove the
   * item from the queue.
   *
   * @param mixed $item Queue item to iterate over
   *
   * @return mixed
   */
  protected function task( $row ) {
      $body_array = getBodies();
      $ttl = '';
      if($row['NewUsed']){
        $ttl .=ucwords($row['NewUsed']).' ';
        $row['NewUsed'] = strtolower($row['NewUsed']);
        $type_id = term_exists( $row['NewUsed'], 'condition' );
        if($type_id){
          $condition_ids = array( $type_id['term_id'] );
        }else{
          $type_id =   wp_insert_term(ucwords($row['NewUsed']), 'condition');
          if($type_id){
            $condition_ids = array( $type_id['term_id'] );
          }else{
            $condition_ids = array();
          }
        }
      }

      if($row['Year']){
        $ttl .=$row['Year'].' ';
        $row['Year'] = strtolower($row['Year']);
        $year_obj = term_exists( $row['Year'], 'ca-year' );
        if($year_obj){
          $year_ids = array( $year_obj['term_id'] );
        }else{
          $year_obj =   wp_insert_term($row['Year'], 'ca-year');
          if($year_obj){
            $year_ids = array( $year_obj['term_id'] );
          }else{
            $year_ids = array();
          }
        }
      }

      if($row['Body_Id']){
        $row['Body_Id'] = $body_array[$row['Body_Id']];
        $row['Body_Id'] = strtolower($row['Body_Id']);
        $body_obj = term_exists( $row['Body_Id'], 'body' );
        if($body_obj){
            $body_ids = array( $body_obj['term_id'] );
        }
        else{
            $body_obj =   wp_insert_term(ucwords($row['Body_Id']), 'body');
            if($body_obj){
                $body_ids = array( $body_obj['term_id'] );
            }
            else{
                $body_ids = array();
            }
        }
      }      
      
      if($row['Body_Style']){
        $row['Body_Style'] = strtolower($row['Body_Style']);
        $body_type_obj = term_exists( $row['Body_Style'], 'body-type' );
        if($body_type_obj){
            $body_type_ids = array( $body_type_obj['term_id'] );
        }
        else{
            $body_type_obj =   wp_insert_term(ucwords($row['Body_Style']), 'body-type');
            if($body_type_obj){
                $body_type_ids = array( $body_type_obj['term_id'] );
            }
            else{
                $body_type_ids = array();
            }
        }
      }

      if($row['Ext_Color']){
        $row['Ext_Color'] = strtolower($row['Ext_Color']);
        $ext_color = term_exists( $row['Ext_Color'], 'exterior-color' );
        if($ext_color){
            $ext_color_ids = array( $ext_color['term_id'] );
        }
        else{
            $ext_color =   wp_insert_term(ucwords($row['Ext_Color']), 'exterior-color');
            if($ext_color){
                $ext_color_ids = array( $ext_color['term_id'] );
            }
            else{
                $ext_color_ids = array();
            }
        }
      }
      
      if($row['Int_Color']){
        $row['Int_Color'] = strtolower($row['Int_Color']);
        $int_color = term_exists( $row['Int_Color'], 'interior-color' );
        if($int_color){
            $int_color_ids = array( $int_color['term_id'] );
        }
        else{
            $int_color =   wp_insert_term(ucwords($row['Int_Color']), 'interior-color');
            print_r($int_color);
            if($int_color){
                $int_color_ids = array( $int_color['term_id'] );
            }
            else{
                $int_color_ids = array();
            }
        }
      }
      
      if($row['Make']){
        $ttl .=ucwords($row['Make']).' ';
        $row['Make'] = strtolower($row['Make']);
        $make_id = term_exists( $row['Make'], 'make' );
        if($make_id){
          $make_ids = array( $make_id['term_id'] );
        }
        else{
          $make_id =   wp_insert_term(ucwords($row['Make']), 'make');
          if($make_id){
            $make_ids = array( $make_id['term_id'] );
          }else{
            $make_ids = array();
          }
        }
      }
      
      if($row['Model']){
          $row['Model'] = strtolower($row['Model']);
        $ttl .=ucwords($row['Model']);
        $model_id = term_exists( $row['Model'], 'serie' );
        if($model_id){
          $model_ids = array( $model_id['term_id'] );
        }
        else{
          $model_id =   wp_insert_term(ucwords($row['Model']), 'serie');
          if($model_id){
            $model_ids = array( $model_id['term_id'] );
          }else{
            $model_ids = array();
          }
        }
      }

      if($row['Fuel']){
          $row['Fuel'] = strtolower($row['Fuel']);
        $fuel_id = term_exists( $row['Fuel'], 'fuel' );
        if($fuel_id){
          $fuel_ids = array( $fuel_id['term_id'] );
        }
        else{
          $fuel_id =   wp_insert_term(ucwords($row['Fuel']), 'fuel');
          if($fuel_id){
            $fuel_ids = array( $fuel_id['term_id'] );
          }else{
            $fuel_id = array();
          }
        }
      }
      
      if($row['Selling_Price']){
          $selling_price = strtolower($row['Selling_Price']);
          $selling_price = str_replace("$", "", $selling_price);
        $selling_price = str_replace(",", "", $selling_price);
        $price_id = term_exists( $selling_price, 'price' );
        if($price_id){
          $price_ids = array( $price_id['term_id'] );
        }
        else{
          $price_id =   wp_insert_term($selling_price, 'price');
          if($price_id){
            $price_ids = array( $price_id['term_id'] );
          }else{
            $price_ids = array();
          }
        }
      }

      if($row['Odometer']){
        $row['Odometer'] = strtolower($row['Odometer']);
        $mileage_id = term_exists( $row['Odometer'], 'mileage' );
        if($mileage_id){
          $mileage_ids = array( $mileage_id['term_id'] );
        }
        else{
          $mileage_id =   wp_insert_term($row['Odometer'], 'mileage');
          if($mileage_id){
            $mileage_ids = array( $mileage_id['term_id'] );
          }else{
            $mileage_ids = array();
          }
        }
      }
      if($row['TransType']){
          $row['TransType'] = strtolower($row['TransType']);
        $trans_id = term_exists( $row['TransType'], 'transmission' );
        if($trans_id){
          $trans_ids = array( $trans_id['term_id'] );
        }
        else{
          $trans_id =   wp_insert_term(ucwords($row['TransType']), 'transmission');
          if($trans_id){
            $trans_ids = array( $trans_id['term_id'] );
          }else{
            $trans_ids = array();
          }
        }
      }

      
      if($row['DriveTrain']){
          $row['DriveTrain'] = strtolower($row['DriveTrain']);
        $drive_id = term_exists( $row['DriveTrain'], 'drive' );
        if($drive_id){
          $drive_ids = array( $drive_id['term_id'] );
        }
        else{
          $drive_id =   wp_insert_term(strtoupper($row['DriveTrain']), 'drive');
          if($drive_id){
            $drive_ids = array( $drive_id['term_id'] );
          }else{
            $drive_ids = array();
          }
        }
      }
      
      if($row['Options']){
       $options = explode("|", $row['Options']);
       $options_ids = array();
       foreach ($options as $option){
        $option_id = term_exists( $option, 'stm_additional_features' );
        if($option_id){
          $options_ids[] = $option_id['term_id'] ;
        }
        else{
          $option_id =   wp_insert_term(ucwords($option), 'stm_additional_features');
          if($option_id){
            $options_ids[] = $option_id['term_id'] ;
          }
        }
        }
      }

      $my_post = array(
        'post_title'    => $ttl,
        'post_name'   => sanitize_title_with_dashes($ttl.' '.$row['Selling_Price'].' '.$row['Vin'],'','save'),
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'listings',
        'tax_input'     => array(
          'make'         => $make_ids,
          'condition'    => $condition_ids,
          'ca-year'    => $row['Year'],
          'body'       => $body_ids,
          'body-type' => $body_type_ids,
          'mileage'       => $row['Odometer'],
          'serie'   => $model_ids,
          'exterior-color'    => $ext_color_ids,
          'interior-color'    => $int_color_ids,
          'price'     => $selling_price,
          'transmission'  => $trans_ids,
          'drive'   => $drive_ids,
          'fuel'  => $fuel_ids,
          'stm_additional_features'   => implode( ',', $options )
        ),
      );

        $row = array_map('strtolower', $row);
        $m = wp_insert_post($my_post);
        $row['Image_Medium'] = implode("|", array_slice(explode("|", $row['Image_Medium']), 0, 9));

        //THE PROBLEM IS AROUND HERE

        if( $m ) {
        $arr_meta = array(
          'stock_number'                   =>  ( ( !empty( $row['Stock_Num'] ) ) ? $row[ 'Stock_Num' ]  : '' ),
          'vin_number'                   =>  ( ( !empty( $row['Vin'] ) ) ? strtoupper($row[ 'Vin' ])   : '' ),
          'serie'                   => ((!empty($row['Model']))? sanitize_title_with_dashes($row[ 'Model']) :''),
          'condition'                   => ((!empty($row['NewUsed']))? $row[ 'NewUsed'] :''),
          'make'                   => ((!empty($row['Make']))? $row[ 'Make'] :''),
          'ca-year'                   => ((!empty($row['Year']))? $row[ 'Year'] :''),
          'body'                    => ((!empty($row['Body_Id']))? sanitize_title_with_dashes($row['Body_Id']) :''),
          'body-type'                    => ((!empty($row['Body_Style']))? sanitize_title_with_dashes($row['Body_Style']) :''),
          'exterior-color'           => ((!empty($row['Ext_Color']))? $row[ 'Ext_Color'] :''),
          'interior-color'           => ((!empty($row['Int_Color']))? $row[ 'Int_Color'] :''),
          'enginecylinders'         => ((!empty($row['Cylinders']))? $row[ 'Cylinders'] :''),
          'enginedisplacement'      => ((!empty($row['Displacement']))? $row[ 'Displacement'] :''),
          'transmission'            => ((!empty($row['TransType']))? $row[ 'TransType'] :''),
          'mileage'                   => ((!empty($row['Odometer']))? $row[ 'Odometer'] :''),
          'price'            => ((!empty($row['Selling_Price']))? $row[ 'Selling_Price'] :''),
          'stm_genuine_price'            => ((!empty($selling_price))? $selling_price :''),
          'msrp'                    => ((!empty($row['MSRP']))? $row[ 'MSRP'] :''),
          'internetprice'           => ((!empty($row['InternetPrice']))? $row[ 'InternetPrice'] :''),
          'bookvalue'               => ((!empty($row['Book_Value']))? $row[ 'Book_Value'] :''),
          'invoice'                 => ((!empty($row['Invoice']))? $row[ 'Invoice'] :''),
          'dateinstock'             => ((!empty($row['DateInStock']))? $row[ 'DateInStock'] :''),
          'description'             => ((!empty($row['Description']))? $row[ 'Description'] :''),
          'transmissionaspeed'      => ((isset($row['TransASpeed']) && !empty($row['TransASpeed']))? $row[ 'TransASpeed'] :''),
          'transmissiondescription' => ((!empty($row['Trans_Descript']))? $row[ 'Trans_Descript'] :''),
          'drive'              => ((!empty($row['DriveTrain']))? $row[ 'DriveTrain']  :''),
          'fuel'                => ((!empty($row['Fuel']))? $row[ 'Fuel'] :''),
          'city_mpg'                 => ((!empty($row['MPGCity']))? $row[ 'MPGCity'] :''),
          'highway_mpg'              => ((!empty($row['MPGHwy']))? $row[ 'MPGHwy'] :''),
          'wheelbasecode'           => ((!empty($row['WheelBase']))? $row[ 'WheelBase'] :''),
          'additional_features'       => ((!empty($row['Options']))? ucwords(str_replace("|", ",", $row[ 'Options']),', ')  :''),
          'stm_car_user'      =>  '3',
          'title'         => 'hide',
          'images'                  => ((!empty($row['Image_Medium']))? $row[ 'Image_Medium'] :''),
          'stm_car_location'    => ((!empty($row['DealerStreet']))? ucwords($row[ 'DealerStreet']) .", ".ucwords($row[ 'DealerCity']).", ".strtoupper($row[ 'DealerState']).", United States" :''),
          'stm_lng_car_admin'   => '-91.38642600000003',
          'stm_lat_car_admin'   => '44.9275779'
        );

        $arr_meta['price'] = str_replace("$", "", $arr_meta['price']);
        $arr_meta['price'] = str_replace(",", "", $arr_meta['price']);

        if($row['Image_Medium']!=''){
          $row['Image_Medium'] = explode('|', $row['Image_Medium']);
          $featured_image = array_shift($row['Image_Medium']);
          $result = media_sideload_image($featured_image, $m, '','id' );
          set_post_thumbnail($m, $result);
        }

        foreach($arr_meta as $meta_k => $meta_v  ){
          if($meta_v  != '')
           if(is_array($meta_v)){
              update_post_meta($m, $meta_k,  $meta_v );
          }else{
            update_post_meta($m, $meta_k, trim($meta_v));
          }
        }    

        }

    return false;
  }

  /**
   * Complete
   *
   * Override if applicable, but ensure that the below actions are
   * performed, or, call parent::complete().
   */
  protected function complete() {
    parent::complete();

    // Show notice to user or perform some other arbitrary task...
  }

}