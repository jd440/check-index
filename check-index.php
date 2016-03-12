<?php
/* check-index.php
 * Première version : 10 03 2016
 * Dernière modification : 0 03 2016
.---------------------------------------------------------------------------.
|  Software: check-index                                                    |
|   Version: 0.1                                                            |
|   Contact: https://github.com/jd440/                                      |
| ------------------------------------------------------------------------- |
|    Author: JD440                                                          |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Creative Commons license (BY-NC-SA )     |
|            http://creativecommons.org/licenses/by-nc-sa/3.0/              | 
|                                                                           |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY.                                                             |
| ------------------------------------------------------------------------- |
|   Licence: Distribué sous licence Creative Commons (BY-NC-SA)             |
|            http://creativecommons.org/licenses/by-nc-sa/3.0/fr/           |
|                                                                           |
| Ce programme est distribué dans l'espoir qu'il sera utile - SANS AUCUNE   |
| GARANTIE.                                                                 |
| ------------------------------------------------------------------------- |
| Ce mini scipt permet de comparer les index entre deux tables de donnée    |
'---------------------------------------------------------------------------'
*/

// définiton table mis à jour
$db_selected_new = "";
$db_host_new = "localhost";
$db_user_new = "";
$db_password_new = "";

// définiton table référence
$db_selected_vierge = "";
$db_host_new = "localhost";
$db_user_new = "";
$db_password_vierge = "";


/* Dans le cas d'un prestashop exclure les tables modules*/
$prefix_table = "ps_";
$exclude_table = ['advice','advice_lang','badge','badge_lang','condition','condition_advice','condition_badge','cronjobs','info','info_lang','layered_category','layered_filter','layered_filter_shop','layered_friendly_url','layered_indexable_attribute_group','layered_indexable_attribute_group_lang_value','layered_indexable_feature','layered_indexable_feature_lang_value','layered_price_index','layered_product_attribute','product_comment','product_comment_criterion','product_comment_criterion_category','product_comment_criterion_lang','product_comment_criterion_product','product_comment_grade','product_comment_report','product_comment_usefulness','statssearch','tab_advice','themeconfigurator','wishlist','wishlist_email','wishlist_product','wishlist_product_cart']; 

if ($prefix_table)
	for($nb =0; $nb <= (count($exclude_table)-1); $nb++)
		$exclude_table[$nb] = $prefix_table.$exclude_table[$nb];

// Se connecte à la tables
function connect_table($db_selected, $db_host = "localhost", $db_user, $db_password){
    $dsn = 'mysql:dbname='.$db_selected.';host='.$db_host;
    try {
        return new PDO($dsn, $db_user, $db_password);
    } catch (PDOException $e) {
        echo 'Connexion échouée : ' . $e->getMessage();
    }
}
function get_index($db, $table){
	$stm = $db->prepare("show index from ".$table);
	$res = $stm->execute();
	$index = $stm->fetch(PDO::FETCH_ASSOC);
	return $index;
}

$db_new = connect_table($db_selected_new, $db_host_new, $db_user_new, $db_password_new);
$db_vierge = connect_table($db_selected_vierge, $db_host_vierge, $db_user_vierge, $db_password_vierge);


$rq = "show TABLES";
$out = "<table border='1'><td> Nom de la table</td><td>nouvelle table</td><td>table de référence</td><td>différence</td></tr>";	
	foreach($db_vierge->query($rq) as $row) {
		if (!in_array($row[0], $exclude_table)){

				$index_new = get_index($db_new, $row[0]);
				$index_vierge = get_index($db_vierge, $row[0]);

				$diff= $result_compare = "";

				if ($index_vierge)
					foreach($index_vierge as $key => $value)
						if (((isset($index_new[$key]))&&(isset($index_vierge[$key])))&&($index_vierge[$key] != $index_new[$key])&&($key <> "Cardinality"))
							$diff .= $key."<br/>";

					$out .= "<tr><td>" . $row[0]. "</td><td>";

					    if ($diff)
						    $out .= "<pre>".print_r($index_new, true)."</pre>";
						elseif ((!isset($index_new))||($index_new  == ""))
							$out .= "Vide";
					

					$out .= "</td><td>";
						if ($diff)
					        $out .= "<pre>".print_r($index_vierge, true)."</pre>";
						elseif ((!isset($index_vierge))||($index_vierge  == ""))
							$out .= "Vide";

					$out .= "</td><td>";
					    if ($diff) $out .= $diff;
						$out .= "</td></tr>";
		}
	}
	$out .= "</table>";
	echo $out;


