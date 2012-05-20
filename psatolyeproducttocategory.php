
<?php
/*
 * 2007-2011 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2011 PrestaShop SA
 *  @version  Release: $Revision: 7541 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_CAN_LOAD_FILES_'))
    exit;




class PSAtolyeProducttoCategory extends Module {
	
    public function __construct() {
        $this->name = 'psatolyeproducttocategory';
        $this->tab = 'others';
        $this->version = 1.0;
        $this->author = 'Bora Yalçın';
        $this->need_instance = 0;
        $this->is_configurable = 1;


        parent::__construct();
        

        $this->displayName = $this->l('Product to Category by PS Atölye');
        $this->description = $this->l('Adds and Removes Products to a Category Fast and Easy');
    }

    public function install() {
        if (parent::install() == false)
            return false;
        return true;
    }

    

    
	
	function getContent(){

		?>
		<div class="conf">
		<?php
		if(Tools::getValue('update_category'))
		{
			if($this->insert_into_category()):?>
				<p><?php echo $this->l('Process Completed');?></p>
			<?php endif;
		}
		?>
		</div>
		<?php

		$categories = Category::getSimpleCategories(Configuration::get('PS_LANG_DEFAULT'));

		?>

		<style>
		form{
			font-size:14px;
		}
		input[type="text"]{
			height:30px;
			width:400px;
			padding:5px;
			font-size:14px;
		}
		.submitbutton,select{
			height:30px;
			padding:5px;
			font-size:14px;
			font-weight:bold;
		}
		</style>
		<form action="<?php echo Tools::safeOutput($_SERVER['REQUEST_URI']);?>" method="post">
			<p><?php echo $this->l('Product IDs to Add or Remove (Comma Seperated List)');?></p>
			<p><input type="text" name="product_ids"/></p>

			<p><?php echo $this->l('The category you want to add/remove the products');?></p>
			<p>
			<select name="category_id">

				<?php	foreach ($categories as $keycategory => $category):?>


				<option value="<?php echo $category['id_category'];?>"><?php echo $category['name'];?> (id: <?php echo $category['id_category'];?>)</option>
			
				<?php endforeach;?>

			</select>
			</p>
			<p><?php echo $this->l('Do you want to add or remove them from the category?');?></p>
			<p>
			<select name="actiontype">
				<option value="add"><?php echo $this->l('Add to Category');?></option>
				<option value="remove"><?php echo $this->l('Remove from Category');?></option>
			</select>
			</p>
			<p><?php echo $this->l('Do you want to add or remove them from the parent categories of selected category? (Except "Home" category)');?></p>
			<p>
			<select name="recursive">
				<option value="true"><?php echo $this->l('Yes, add/remove from parents too');?></option>
				<option value="false"><?php echo $this->l('No, add/remove from this one only');?></option>
			</select>
			</p>

			<input type="submit" class="submitbutton" value="<?php echo $this->l('Do It!');?>" name="update_category"/>
		</form>
		<?php

	}

	

	

	public function insert_into_category(){

		//check for actiontype
		if(Tools::getValue('actiontype') === 'add') {
			$action = 'add';
		} elseif(Tools::getValue('actiontype') === 'remove') {
			$action = 'remove';
		} else {
			return false;
		}

		$recursive = false;
		if(Tools::getValue('recursive') == true)
			$recursive = true;

		$id_category = Tools::getValue('category_id');
		$id_products = Tools::getValue('product_ids');

		//check category exists
		if(true != Category::categoryExists($id_category)) {
			return false;
		}

		//current category
		$category = new Category($id_category);

		if($recursive === true) {
			//parents for recursive
			$parents = $category->getParentsCategories();
			//parents to array
	    $parent_ids = array();
	    foreach($parents as $p)
	    {
	    	//skip the Home category		
    		if($p == 1)continue;

        $parent_ids[] = $p['id_category'];
	    }
	  }

		//products to array
		$products = explode(',',trim($id_products));

		//add remove
		foreach ($products as $keyproduct => $product) {
			
			$theproduct = new Product( $product );

			if($action == 'add') {
				$theproduct->addToCategories( array($id_category) );

				//add to parent categories too
				if($recursive === true && !empty($parent_ids) && $category->level_depth != 0) {
        	$theproduct->addToCategories($parent_ids);
        }
				echo '<p>'.$theproduct->name[Configuration::get('PS_LANG_DEFAULT')].' is added</p>';
			}
			elseif($action == 'remove') {
				$theproduct->deleteCategory( $id_category );

				//remove from parent categories too
				if($recursive === true && !empty($parent_ids) && $category->level_depth != 0) {
					foreach ($parent_ids as $keyparent => $parent_id) {
						
        		$theproduct->deleteCategory($parent_id);
					}
        }

				echo '<p>'.$theproduct->name[Configuration::get('PS_LANG_DEFAULT')].' is removed</p>';
			}
		}

		return true;
		
	}
}
