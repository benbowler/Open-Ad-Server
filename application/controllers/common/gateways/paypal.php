<?php
   $form = array(
         'id'          => 'paypal',
         'name'        => 'paypal',                   
         'view'        => "common/gateways/paypal.html",
         'success_view' => "advertiser/add_funds/success.html",
         'vars' => array(
         'COMMISSION' => $row['fund_comm'],
         'MINIMAL_PAYMENT' => type_to_str($row['minimal_payment'], 'money')
   ),
         "fields"      => array(                     
            "amount" => array(
               "display_name"     => "Amount",
               "id_field_type"    => "money",
               "form_field_type"  => "text",
               "validation_rules" => "required|float[2]|positive"
            ),
            "agree" => array(
               "form_field_type" => "checkbox"               
            )                     
         ) 
      );
   $forms .= $this->form->get_form_content("create", &$form, $this->input, &$this);
?>