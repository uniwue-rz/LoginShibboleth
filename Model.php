<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\LoginLdap;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Date;

/**
 * The UsersManager API lets you Manage Users and their permissions to access specific websites.
 *
 * You can create users via "addUser", update existing users via "updateUser" and delete users via "deleteUser".
 * There are many ways to list users based on their login "getUser" and "getUsers", their email "getUserByEmail",
 * or which users have permission (view or admin) to access the specified websites "getUsersWithSiteAccess".
 *
 * Existing Permissions are listed given a login via "getSitesAccessFromUser", or a website ID via "getUsersAccessFromSite",
 * or you can list all users and websites for a given permission via "getUsersSitesFromAccess". Permissions are set and updated
 * via the method "setUserAccess".
 * See also the documentation about <a href='http://piwik.org/docs/manage-users/' target='_blank'>Managing Users</a> in Piwik.
 */
class Model extends \Piwik\Plugins\UsersManager\Model
{

	/**
	*
	* 
	*@return which access group user will be set.
	*/
	public function getUserAccessShib(){
		$is_view = False;
		$is_super_user = False;
		$memberships = array();
		$groups = $_SERVER["groupMembership"];
		$groups_seprated = explode(";", $groups);
		foreach($groups_seprated as $group){
			$group_as_array = explode(",", $group);
			if(in_array("cn=RZ-Piwik-Admin", $group_as_array) && !$is_super_user){
				$is_super_user = True;
			}
			if(in_array("cn=RZ-Piwik-View", $group_as_array) && !$is_super_user && !$is_view ){
				$is_view = True;
			}

		}
		if($is_view){
			array_push($memberships, "view");
		}
		if($is_super_user){
			array_push($memberships, "superUser");
		}
		return $memberships;
	}

	public function getUser($login){
		$memberships = $this->getUserAccessShib();
		if(sizeof($memberships)==0){
			return array();
		}
		else{
			if(!$this->userExists($_SERVER["REMOTE_USER"])){
				$this->addUser($_SERVER["REMOTE_USER"], 	$this->generatePassword(8), $this->getEmail(), $_SERVER["fullName"], $_SERVER["Shib-Session-ID"], Date::now()->getDatetime());
				if(in_array("view", $memberships) && !in_array("superUser", $memberships)){
				$access = "view";
				}
				if(sizeof($this->getSitesAccessFromUser($_SERVER["REMOTE_USER"]))==0 && !in_array("superUser", $memberships)){
					$this->addUserAccess($_SERVER["REMOTE_USER"],$access,array(2));
				}
			}
			return array(
			'login' => $_SERVER["REMOTE_USER"],
			'alias' => $_SERVER["fullName"],
			'email' => $this->getEmail(),
			'token_auth' =>$_SERVER["Shib-Session-ID"],
			'superuser_access' => $this->hasSuperAccess($memberships)
			);
		}
	}

	//get email for the user that have email.
	public function getEmail(){
		$mail = "dummy@uni-wuerzburg.de";
		if (in_array("mail", $_SERVER)){
			$mail = $_SERVER["mail"];
		}
		return $mail;
	}

    /**
     * @param $length
     * @return string
     */
    private function generatePassword($length)
    {
        $chars = "234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $i = 0;
        $password = "";
        while ($i <= $length) {
            $password .= $chars{mt_rand(0, strlen($chars) - 1)};
            $i++;
        }
        return $password;
    }

	public function hasSuperAccess($memberships){
		$hasSuperAccess = 0;
		if(in_array("superUser", $memberships)){
			$hasSuperAccess = 1;
		}
		return $hasSuperAccess;
		}
}