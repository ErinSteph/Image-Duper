<?php
/*
||          //                           || Script By: Seff Preston
||         //   ||                //     || Script Premise: If U Seek Amy
||        //    ||               //      || ~some informational assistance from Nymph
||       //     ||              //       || ~Updated for 2016 by Erin
||      //      ||)))))        //        || Special Thank you to the circlejerks
||     //       ||     ))     //         || ~You make my kokoro go doki doki
||    //        ||      ))   //          ||
||   //         ||      ))  //           || P.S.
||  //          ||     ))  //            || I love you for making the API Mootykins
|| //           |||||))   //             || Now, just give peace a chance?
|*/
//set available boards
$bArray = array("3","a","aco","adv","an","asp","b","biz","c","cgl","ck","cm","co","d","diy","e","f","fa","fit","g","gd","gif","h","hc","his","hm","hr","i","ic","int","jp","k","lgbt","lit","m","mlp","mu","n","news","o","out","p","po","pol","qa","r","r9k","s","s4s","sci","soc","sp","t","tg","toy","trash","trv","tv","u","v","vg","vp","vr","w","wg","wsg","wsr","x","y");

if(!isset($_POST['id']))
{
	//Thread Insert Form
	?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Seff's Image Catcher</title>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    </head>
    <body>
		<form name="imageCatcher" method="POST">
        Select a Board:<br/>
        <select name="board">
        <?php
		foreach($bArray as $b)
		{
			echo("<option value=\"$b\">$b</option>");
		}
		?>
        </select><br/><br/>
        Image ID with extension (ex: 1363032507873.png):<br/>
        <input type="text" name="id" /><br/><br/>
        Number of copies (default 1):<br/>
        <input type="text" name="copies" /><br/><br/>
        <input type="submit" value="Submit">
        </form>
    </body>
    </html>
    <?php
} else {
	
	//clean input data with regular expressions
	$board = preg_replace("/[^a-z]/", "", strtolower($_POST['board']));
	$id = preg_replace("/[^0-9a-z\.]/","", $_POST['id']);
	$copies = preg_replace("/[^0-9]/","", $_POST['copies']);
	if($copies > 100){ $copies = 100; }
	if($copies < 1){ $copies = 1; }
	if(!is_numeric($copies)){ $copies = 1; }
	
	//set the thread url to load
	$url = "http://i.4cdn.org/$board/$id";
	
	//load the thread from the 4chan api, @ supresses any errors
	$image = @file_get_contents($url);
	if($image === false){ echo("Unable to load the image"); } else {
		
		//create an image resource
		$original = imagecreatefromstring($image);
		
		//repeat the following actions for each image
		for($i=0; $i<$copies; $i++)
		{	
		
			//generate a copy of the same image resource
			$copy = imagecreatetruecolor(imagesx($original), imagesy($original));
    		imagesavealpha($copy, true);
			
			//make the background transparent
			$trans_color = imagecolorallocatealpha($copy, 0, 0, 0, 127);
    		imagefill($copy, 0, 0, $trans_color);
			
			//give the image a random height/width
			$height1 = imagesy($original);
			$height2 = imagesy($original)+rand(1,5);
			$width1 = imagesx($original);
			$width2 = imagesx($original)+rand(1,5);
			//copy the original image into the copy resource with the new proportions
			imagecopyresized($copy,$original,0,0,0,0,$width2,$height2,$width1,$height1);
			
			//repeat the following actions for each modification of the image
			for($j=0; $j<=250; $j++)
			{
				//generate random coordinates for pixel replacement
				$pixelx = rand(0, imagesx($copy));
				$pixely = rand(0, imagesy($copy));
				
				//retrieve the current rgb values of that coordinate and modify them
				$rgb = imagecolorat($copy, $pixelx, $pixely);
				$r = ($rgb >> 16) & 0xFF; $r += rand(-5, 5);
				$g = ($rgb >> 8) & 0xFF; $g += rand(-5, 5);
				$b = $rgb & 0xFF; $b += rand(-5, 5);
				
				//clean the rgb values
				if($r > 255) { $r=255; } if($g > 255) { $g=255; } if($b > 255) { $b=255; }
				if($r < 0) { $r = 0; } if($g < 0) { $g = 0; } if($b < 0) { $b = 0; }
				
				//insert modified pixel color
				$color = imagecolorallocate($copy, $r, $g, $b);
				
				//modify a random pixel of the image copy
				imagesetpixel($copy, $pixelx, $pixely, $color);
				
			}
			
			//start buffering to cature image data
			ob_start();
			//generate the image as a png
			imagepng($copy);
			//capture the image
			$contents =  ob_get_contents();
			//end buffering
			ob_end_clean();
			//output the image
			echo "<img src='data:image/png;base64,".base64_encode($contents)."' />";
			//clean the image
			imagedestroy($copy);

		}
	}
}
?>
