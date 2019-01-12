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
require("global.settings.php");
require_once("hadith.loader.php");
?>
<?php header('Access-Control-Allow-Origin: http://alteb-alnabawi.com'); ?>

<!DOCTYPE html>
<html lang="en">
  <head>

    <meta charset="utf-8">
    <title> Tibb Annabwi Semantic Search System (BETA) </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tibb Annabwi is a Smart Search, Exploration, System for the prophet medicine ">
<!--    <meta name="google-site-verification" content="LhSFFAyvZMENLI3xCG4OcpU_db2avxyZiBaq72zLX7A" />-->
	<script type="text/javascript" src="<?=$JQUERY_PATH?>" ></script>
	<script type="text/javascript" src="<?=$MAIN_JS_PATH?>"></script>
	<script type="text/javascript" src="<?=$D3_PATH?>"></script>
	<script type="text/javascript" src="<?=$TINYSORT_PATH?>"></script>
	<script type="text/javascript" src="<?=$TINYSORT_JQ_PATH?>"></script>	
	<script type="text/javascript" src="<?=$JQUERY_TAGCLOUD_PATH?>" ></script> 


	<link rel="stylesheet" href="<?=$QE_STYLE_PATH."?bv=".$BUILD_VERSION?>" />
	<link rel="icon" type="image/png" href="./favicon.png">
      	 
	<script type="text/javascript">
	</script>


     <?php 
		require("in-head.php");
	 ?>


  </head>
  <body>
  

			  	
     <?php 
		require("header.php");
	 ?>
  		
  
  <div id='mainpage-maincontainer'>

        <div id='options-area'>

        <?php 
                include_once("header.menu.php");
        ?>

                <table>
                        <tr>
                                <td>
                                        <input id="search-field" type="text" value="" ></input>
                                </td>
                                <td>
                                        <input  type="submit" id="doSearch"  onclick='doSearch()' value="Search"/>
                                </td>
                        </tr>
                        <tr>
                                <td colspan='2'>


                                </td>
                        </tr>
                </table>


        </div>	
        <div id="loading-layer">
                Loading ...
        </div>
        <div id='content-area'>
                <div id='main-page-examples-area'>
<!--                <h1 id='main-page-main-message'>Search and Explore the Tibb Annabwi like never before ...</h1>-->

                <div id='main-page-try'>
<!--                 <b>Click</b>
                 to try the following examples-->

                 </div> 

                <table id='main-page-examples-table'>
<!--                        <tr>
                                <td>
                                        One Word

                                </td>
                                <td style="position:relative">
                                        <a href="?q=Muhammad" class='main-page-example-item'>Muhammad</a>
                                        /
                                        <a href="?q=محمد" class='main-page-example-item'>محمد</a>
                                         <br>
                                        <img src='./images/hand-click-icon.png' id='main-page-click-icon'/> 
                                </td>
                        </tr>-->
<!--                        <tr>
                                <td>
                                        Multiple Words
                                </td>
                                <td>
                                        <a href="?q=Heaven Hellfire" class='main-page-example-item'>Heaven Hellfire</a>
                                        /
                                        <a href="?q=الجنة و النار" class='main-page-example-item'>الجنة و النار</a>
                                        <br>
                                        <span class='note'>Verses containing Heaven OR Hellfire</span>
                                </td>
                        </tr>-->
<!--                        <tr>
                                <td>
                                        Phrases
                                </td>
                                <td>
                                        <a href="?q=%22Those who believe%22" class='main-page-example-item'>"Those who believe"</a>
                                        /
                                        <a href="?q=<?php echo urlencode('"الذين آمنوا"')?>" class='main-page-example-item'>"الذين آمنوا"</a>
                                        <br>
                                         <span class='note'>Should be enclosed in quotes ""</span>
                                </td>
                        </tr>-->
                       
                </table>



                </div>
        </div>
   </div>
   

	<script type="text/javascript">


		$(document).ready(function()
		{

			<?php if ( !empty($query) ):?>
				$("#search-field").val(("<?= addslashes($query)?>"));
				doSearch();
				
			<?php else:?>

			$("#options-area").css("margin-top","100px");
			var intervalID = setInterval(function(){ $("#main-page-click-icon").toggle(); },"100");

			setTimeout(function()
			{ 
                            clearInterval(intervalID);
                            $("#main-page-click-icon").toggle();
			 },500);
			 
			<?php endif;?>


		});

		function clientSortResults(listElementsClass)
		{
		
                    var selectedField = $("#qa-sort-select option:selected").val();

                    var currentOrder = $("#qa-sort-select option:selected").attr("sortorder");
                    $('.'+listElementsClass).tsort({attr:selectedField, order: currentOrder});

		}	

	function changeDefaultQuranScript()
		{
                    var query = $("#search-field").val();
                    var script = $("#qa-script-select option:selected").val();
                    showResultsForQueryInSpecificDiv(query,"","true","result-verses-area",script);
		}
	
        /**
         * search button clicked; search with user key words
        */        
        function doSearch() {
            var query = $("#search-field").val();
            // if query is empty
            if (query=='' || query.trim().length ==0 )
            {
                $("#search-field").focus();
                return;
            }
            // else, show loading icon
            $("#loading-layer").show();
            $("#content-area").html("");//div that contains table examples

            destroyGraph();
            $("#options-area").css("margin-top","40px");
            $.ajaxSetup({
                    url:  "./search/index.php?q="+encodeURIComponent(query),
                    global: false,
                    type: "GET"

              });
              //handle ajax respones and error
            $.ajax({
                timeout: 300000,
                success: function(prepareRes)
                        {
                            
                            $("#loading-layer").hide();
                            $("#content-area").html(prepareRes);
                     },
                error: function (xhr, ajaxOptions, thrownError)
                {
                       $("#content-area").html("<center>Error occured !</center>");
                       $("#loading-layer").hide();
                }
                        });
             }


    	$("#search-field").keyup(function(e){ 
		    var keyCode = e.which; 
		    
		    if(keyCode==13)
		    {
		    	e.preventDefault();
		      	$("#doSearch").click();
		    } 
		});



	</script>
        
	<div id='truth-area' >
	Caution: in addition to the beta-experimental nature of this website,<br> it is a human endeavour which can't be perfect and should NOT be considered truth or fact source 
	</div>
	<?php 
		require("footer.php");
	?>
	

	
	
  </body>
</html>







