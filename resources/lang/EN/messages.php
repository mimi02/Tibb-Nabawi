<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Resource {
    function Resource(){
        
    }
    
        var $website_title = '';
        var $website_desc= '';
        var $header_home ='';
        var $header_resources='';
        var $header_credits= '';
        var $header_about ='';
        var $header_contact ='';
        var $menu_search ='';
        var $menu_explore='';
        var $prophets_treatment = 'Prophet\'s treatment';
        var $prophets_material = 'Prophet\'s material used';
        var $modern_treatment = 'Modern treatment';
        var $modern_material = 'Modern material';
        var $medical_condition = 'Medical condition';
        var $h_ref ='ref.';
        var $modern ='Modern medicine';
        var $prophets = 'Prophet\'s medicine';
         var $treatment ='Treatment';
        var $material = 'Material';
        
function lang($phrase){
      static $en =  array(
        'website_title'=>'',
        'website_desc'=>'',
        'header_home'=>'',
        'header_resources'=>'',
        'header_credits'=>'',
        'header_about'=>'',
        'header_contact'=>'',
        'menu_search'=>'',
        'menu_explore'=>'',
        'prophets_treatment' => 'Prophet\'s treatment',
        'prophets_material' => 'Prophet\'s material used',
        'modern_treatment' => 'Modern treatment',
        'modern_material' => 'Modern material',
        'medical_condition' => 'Medical condition',
        '' => '',
        '' => '',
        '' => '',
        '' => '',
    );
    return $en[$phrase];
}
}
