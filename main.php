<?php
require_once ("const.php");

function getMemberPics($group_id) {
	// Test before updating any fields
	// https://developers.facebook.com/tools/explorer/145634995501895/?method=GET&path=255834461424286%2Ffeed%3Ffields%3Dfull_picture%2Ccaption%2Cname%2Cmessage%2Clink%2Cfrom%2Ccreated_time%2Ccomments.limit(999)%7Bmessage%2Ccreated_time%2Cfrom%2Ccomments.limit(999)%7Battachment%2Cfrom%2Cmessage%2Clike_count%7D%2Cattachment%7D&version=v2.8
	$url = "https://graph.facebook.com/" .
			$group_id. "/members?fields=picture&limit=999".
			"&access_token=" . FB_ACCESS_TOKEN;

	$res = file_get_contents($url);
	return json_decode($res);
}

/**
 * Get profile picture from facebook (without redirection)
 */
function FBgetProfilePicture($user_id) {
	$url = "https://graph.facebook.com/" .
			$user_id.
			"/picture?height=300&redirect=false";
			//"&access_token=" . FB_ACCESS_TOKEN;

	$res = file_get_contents($url);
	$redArr = json_decode($res);

	if (!isset($redArr->data)) {
		return false;
	}

	return $redArr->data->url;
}

function processFeed($fbFeed) {
	foreach ($fbFeed->data as $key => $value) {
		$url = $value->picture->data->url;
		$id = $value->id;

		print("Downloading $id.jpg ...\n");

		/* Save it to file */
		file_put_contents(FB_GROUP_ID. "/$id.jpg", fopen($url, 'r'));
	}
}

function main() {
	mkdir(FB_GROUP_ID);

	$fbFeed=null;

	while (true) {
		if ($fbFeed===null) {
			$fbFeed = getMemberPics(FB_GROUP_ID);
		} else if (isset($fbFeed->paging->next)){
			print($fbFeed->paging->next);
			$fbFeed = json_decode(file_get_contents($fbFeed->paging->next));
		} else {
			break;
		}

		if(!isset($fbFeed->data)) {
			break;
		}

		processFeed($fbFeed);

	}
}

main();

?>
