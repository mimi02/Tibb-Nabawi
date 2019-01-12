<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Resource{
    
    function Resource(){
        
    }
    
         var $website_title = 'الطب النبوي';
        var $website_desc= '';
        var $header_home ='الصفحة الرئيسية';
        var $header_resources='';
        var $header_credits= '';
        var $header_about ='عن الموقع';
        var $header_contact ='تواصل معنا';
        var $menu_search ='ابحث';
        var $menu_explore='استكشف';
        var $prophets_treatment = 'علاج من السنة';
        var $prophets_material = 'مواد من السنة';
        var $modern_treatment = 'علاج حديث';
        var $modern_material = 'مواد حديثة';
        var $medical_condition = 'الحالة الطبية';
        var $h_ref ='المصدر';
        var $modern ='الطب الحديث';
        var $prophets = 'الطب النبوي';
        var $treatment ='علاج';
        var $material = 'مواد';
        
    function lang($phrase){
      static $ar =  array(
        'website_title'=>'الطب النبوي',
        'website_desc'=>'',
        'header_home'=>'الصفحة الرئيسية',
        'header_resources'=>'موارد',
        'header_credits'=>'',
        'header_about'=>'عن الموقع',
        'header_contact'=>'تواصل معنا',
        'menu_search'=>'بحث',
        'menu_explore'=>'استكشف',
        'prophets_treatment' => 'علاج من السنة',
        'prophets_material' => 'مواد من السنة',
        'modern_treatment' => 'علاج حديث',
        'modern_material' => 'مواد حديثة',
        'medical_condition' => 'الحالة الطبية',
        '' => '',
        '' => '',
        '' => '',
        '' => '',
    );
    return $ar[$phrase];
}

}