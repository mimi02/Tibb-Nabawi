<?php 
#   PLEASE DO NOT REMOVE OR CHANGE THIS COPYRIGHT BLOCK
#   ====================================================================
#
#    Quran Analysis (www.qurananalysis.com). Full Semantic Search and Intelligence System for the Quran.
#    Copyright (C) 2015  Karim Ouda
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#    You can use Quran Analysis code, framework or corpora in your website
#	 or application (commercial/non-commercial) provided that you link
#    back to www.qurananalysis.com and sufficient credits are given.
#
#  ====================================================================
require_once("../global.settings.php");
require_once("../libs/sparql.queries.lib.php");
require_once("../libs/search.lib.php");
require_once("../libs/core.lib.php");
$time1 = time();
$query = $_GET['q'];

//LOAD ONTOLOGY FROM CACHE
$MODEL_TEBB_ONTOLOGY =  apc_fetch("MODEL_TEBB_ONTOLOGY");
$sparql = getSPARQLEngine();// apc_fetch("sparql");

function searchConcepts($sparql, $keyword){
    global $modern_treatment_class_name, $prophet_treatment_class_name;
    
    //search keyword accross illnesses
    $isIllness = searchIllnesses($sparql, $keyword);
    //echo('keyword is illness: '.$isIllness);
    foreach($isIllness as $illness){
        $label = extractClassName($illness->class);//cleanQuery
           ?>
        <div class='result-aya-container'> 
            <div class='result-aya' >
      <?php  // echo(' '. $label."\n<br>"); ?>
          </div>
        </div>
         <?php
        
         $mtreats = getIllnessTreatment($sparql, $label, $modern_treatment_class_name);
         $ptreats = getIllnessTreatment($sparql, $label, $prophet_treatment_class_name);
        // echo "$treats";
        
            
             ?>
             <div class='result-aya-info'  > 
                 <table class='result-table'>
              <tr>
                  <th> </th>
                <th>Prophet Time</th>
                <th>Modern Time</th> 
              </tr>
                     
              <tr>
                  <th>Treatment </th>
                   <td> <?php foreach($ptreats as $treat){
                  $t = extractClassName ($treat->treatment); echo $t ." <br>"; } ?></td> <!-- prophet treatment -->
                  <td> <?php   foreach($mtreats as $treat){
                  $t = extractClassName($treat->treatment); echo $t ." <br>"; } ?> </td> <!-- modern treatment -->
                 
             </tr>
             <tr>
                 <th>Resources </th>
                 <td> comment:</td>
                 <td> Ontobee term</td>
                 
             </tr>
             </table>
             </div>
             <?php
         
    }
    //search keyword accross treatments
/**    $isTreatment = searchTreatments($sparql, $keyword);
    foreach($isTreatment as $material){
         $label = substr(strchr($material->class, "#"),1);
         ?>
        <div class='result-aya-container'> 
            <div class='result-aya' >
      <?php   echo('<strong> treatmemt: </strong>'.$label ."\n<br>"); ?>
          </div>
        </div>
         <?php
         $illnesses = getIllnessesTreatedBy($sparql,$material);
         foreach($illnesses as $illness){
             $t = substr(strchr($illness->illness, "#") ,1); 
             ?>
             <div class='result-aya-info'  > 
             <?php  echo "$label <strong> treates: </strong> ".$t ." <br>"; ?>
             
             </div>
             <?php
         }
    }*/
    //search keyword accross materials
   /** $isMaterial = searchMaterials($sparql, $keyword);
    foreach($isMaterial as $material){
         $label = substr(strchr($material->class, "#"),1);
         ?>
        <div class='result-aya-container'> 
            <div class='result-aya' >
        <?php
        echo(' <strong> material: </strong>'.$label ."\n<br>");
        ?>
                </div>
        </div>
        <?php
         
         $illnesses = getIllnessesTreatedByMaterial($sparql,$label);
         foreach($illnesses as $illness){
             $t = substr(strchr($illness->illness, "#") ,1);
             ?>
            <div class='result-aya-info'> 
                <?php  echo "$label <strong> treates: <strong>".$t ." <br>"; ?>
             </div>
   <?php
         }
    }*/
}

cleanQuery($query);
searchConcepts($sparql, $query);

function cleanQuery($query){
    if ( preg_match("/\?|؟/", $query)>0  ){
            $query = preg_replace("/\?|؟/", "", $query);
    }
    
    if ( !isSimpleQuranWord($query)){
      $query = convertUthamniQueryToSimple($query);
    }
    
    $query = cleanAndTrim($query);
    return $query;
}

function nWordSearch($sparql, $query){
    $tok = strtok($string, " ");
    while ($tok !== false) {
         $tok = strtok(" ");
         
    }
}



?>

