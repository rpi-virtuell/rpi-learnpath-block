<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class learnpath{

    public $id = 'learnpath';
    public $data;
    public $saved_data;
    public $config;
    public $locdata;
    public $vw = 800; //viewport width
    public $vh = 400; //viewport width
    public $r  = 50;  //radius
    public $ordered_slugs  = array();  //Reihenfolge der Stationen
    public $saved_slugs  = array();  //Reihenfolge der Stationen
    public $line_color = "#c0c0c0";
    public $circle_color = "#fdd78b";
    public $info_box_offset = 0;
    public $codeword_label = "Ein Lösungswort bringt dich weiter";
    public $success_message = "Glückwunsch du hast es geschafft!";
    public $base_url;
    public $background_img;
    public $bg_left;
    public $bg_top;
    public $is_custom_bg;
    public $need_recalc=false;


	function __construct($attributes, $edit = false) {

		$this->base_url = RPI_Learnpath::$plugin_url;



	    $resources = $attributes['resources'];

	    if(isset($attributes['svgbackground']['url'])){

		    $bg_url = $attributes['svgbackground']['url'];
		    $this->is_custom_bg = true;
        }else{
		    $bg_url = $this->base_url.'map.jpg';
		    $this->is_custom_bg = false;
        }


		$this->config['title'] = isset($attributes['title'])?$attributes['title']:$this->id;
		$this->config['radius'] = isset($attributes['radius'])?intval($attributes['radius']):$this->r;
		$this->config['svgbackground'] = $bg_url;
		$this->config['strict'] = isset($attributes['strictcode']) ? $attributes['strictcode'] : false;
		$this->config['bg_color'] = isset($attributes['bg_color']) ? $attributes['bg_color'] : 'transparent';


		$this->r = $this->config['radius'];
		$this->background_img = $this->config['svgbackground'];
		$this->bg_color = $this->config['bg_color'];



		$key = $this->id.'_';


		if($edit){

			$this->saved_config = get_post_meta(get_the_ID(),$key.'_config',true);
			if($this->saved_config){

				$this->saved_slugs = $this->saved_config['ordered_slugs'];
				if($this->saved_config['r'] != $this->r){
					$this->need_recalc = true;
				}else{
					$this->bg_top = $this->saved_config['bg_top'];
					$this->bg_left = $this->saved_config['bg_left'];
                }

			}else{
				$this->need_recalc = true;
            }
			$this->saved_data = get_post_meta(get_the_ID(),$key,true);
			if($this->saved_data[0]["cx"]<1){
				$this->need_recalc = true;
            }

			if($this->need_recalc){
				$this->bg_left = rand(10,1000);
				$this->bg_top = rand(10,1300);
            }

			$this->data = $this->map_data($resources);
		    $this->save_config($key,$this->data);

        }else{
			$this->load_config($key);
        }
    }

    function save_config($key){

	    $this->config['vw']=$this->vw;
	    $this->config['vh']=$this->vh;
	    $this->config['r']=$this->r;
	    $this->config['line_color']=$this->line_color;
	    $this->config['circle_color']=$this->circle_color;
	    $this->config['info_box_offset']=$this->info_box_offset;
	    $this->config['ordered_slugs']=$this->ordered_slugs;
	    $this->config['bg_top']=$this->bg_top;
	    $this->config['bg_left']=$this->bg_left;

	    update_post_meta(get_the_ID(),$key.'_config',$this->config);
	    update_post_meta(get_the_ID(),$key,$this->data);

    }

	function load_config($key){

	    $this->config = get_post_meta(get_the_ID(),$key.'_config',true);

		if(!isset($this->config['vw'])){
			return;
		}

		$this->vw = $this->config['vw'];
		$this->vh = $this->config['vh'];
		$this->r = $this->config['r'];
		$this->line_color = $this->config['line_color'];
		$this->circle_color = $this->config['circle_color'];
		$this->info_box_offset = $this->config['info_box_offset'];
		$this->ordered_slugs = $this->config['ordered_slugs'];
		$this->bg_left = $this->config['bg_top'];
		$this->bg_top = $this->config['bg_left'];

		$this->data = get_post_meta(get_the_ID(),$key,true);

	}

	function map_data($resources){



	    $data = array();

	    $i = 0;

	    foreach ($resources as $entry){

		    $i ++;

		    $title = isset($entry['label'])?$entry['label']:'Station' . $i;

	        $slug = $this->get_slug($title);


	        $code = isset($entry['check'])?$entry['check']:false;

		    if($this->config['strict'] && !$code){
		        $img = RPI_Learnpath::$plugin_url."stop.jpg";
            } else{
		        $img = isset($entry['image'])?$entry['image']['url']:RPI_Learnpath::$plugin_url."star.png";
            }


		    $data[] = array(
			    "title"         => $title,
			    "code"          => $code,
			    "description"   => isset($entry['info'])?$entry['info']:'Noch keine Aufgabenstellung' . $i,
			    "cx"             => 0,
			    "cy"             => 0,
			    "r"             => $this->r,
			    "img"           => $img,
			    "slug"          => $slug,
			    "class"         => "quest"
		    );



		    $this->ordered_slugs[]=$slug;
        }

	    $slug = $this->get_random_slug();

	    $data[] = array(
		    "title" => "Ziel",
		    "code" => "",
		    "description" => $this->success_message,
		    "cx"     => 0,
		    "cy"     => 0,
		    "r"             => $this->r,
		    "img"   => "{$this->base_url}ziel.png",
		    "slug"  => $slug,
            "class" => "finished"
	    );

	    $this->ordered_slugs[]=$slug;
		$vh =0;

		if($this->saved_slugs == $this->ordered_slugs){

		    for ( $i = 0; $i < count( $data ); $i ++ ) {
		        $data[$i]['cx'] = $this->saved_data[$i]['cx'];
		        $data[$i]['cy'] = $this->saved_data[$i]['cy'];
		        $data[$i]['bx'] = $this->saved_data[$i]['bx'];
		        $data[$i]['by'] = $this->saved_data[$i]['by'];
		        $data[$i]['r'] = $this->saved_data[$i]['r'];

			    $vh = ($vh < $data[$i]['cy'])? $data[$i]['cy']:$vh;
			    $r = $data[$i]['r'];
            }
		    $this->vh = $vh+$r+$this->info_box_offset+10;

        }else{
			$this->need_recalc = true;
        }
		if( $this->need_recalc){
			return $this->calc_circle_points($data);
		}
		return $data;



    }


    function calc_circle_points($data){

	    $vw = $this->vw;
	    $r  = $this->r;
	    $d  = $r * 2.8;

	    $x   = $r+10;
	    $y   = $r + rand( 1, ( $r ) )+10;
	    $yh  = $r;
	    $vh = $rt =0;
	    $loc = array();


	    $last_x = 0;
	    $last_y = 0;

        $n = count( $data );

	    for ( $i = 0; $i < $n; $i ++ ) {

	        if($rt == 1){
		        $x -= rand( 1, $d/2 );
            }else{
		        $x += rand( 1, $d/2 );
            }

		    //$r = rand( $r-10, $r+10 );
		    $loc[ $i ] = array(
			    "r" => $r,
			    "x" => $x,
			    "y" => $y,
                "x2" => $last_x,
			    "y2" => $last_y
		    );
		    $last_x = $x;
		    $last_y = $y;

		    if($rt == 1){
			    $x -= $d;
		    }else{
			    $x += $d;
            }


		    if ( $x > ( $vw - $d ) && ($i+1<$n)) {
			    $yh += $d + $r;
			    $rt = 1;
			    $x -= 2*$r;
			    //$x  = ( $rt == 1 ) ? $r + rand( 1, $d ) : $r;
		    }elseif($x - $d < 0 && ($i+1<$n) ){
			    $yh += $d + $r;
			    $rt = 0;
			    $x += 2*$r;
            }

		    $y = $yh + intval(rand( 1, $r ));


		    $vh = ( $y > $vh ) ? $y : $vh;

	    }



	        $this->vh = $vh+$r+$this->info_box_offset+10;

		    for ( $i = 0; $i < count( $loc ); $i ++ ) {
			    $data[ $i ]['cx'] = $loc[ $i ]['x'];
			    $data[ $i ]['cy'] = $loc[ $i ]['y'];
			    $data[ $i ]['bx'] = $loc[ $i ]['x2'];
			    $data[ $i ]['by'] = $loc[ $i ]['y2'];
			    $data[ $i ]['r'] = $loc[ $i ]['r'];
		    }

	    return $data;


    }
    function get_slug($title){
	   return sanitize_title($title);
    }
	function get_random_slug(){

	    $n = count($this->saved_slugs);


		if($n>0){
	        return $this->saved_slugs[($n - 1)];
        }

        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return strtolower(substr(implode($word), 0, 10));

    }


    function draw_clipPath($node){

	    $title = $node['title'];
        $x= $node['cx'];
	    $y = $node['cy'];
	    $r = $node['r'];
	    $slug = $node['slug'];

        $circle = '<clipPath id="clip_%s"><circle cx="%d" cy="%d" r="%d"/></clipPath>';
        return sprintf($circle,$slug,$x, $y,$r);

    }

	function draw_defs(){


		$out = array();

	    foreach ($this->data as $node){

		    $out[] = $this->draw_clipPath($node);

        }

	    echo '<defs>'."\n",implode("\n",$out)."\n".'</defs>'."\n";

	}

	function draw_line($node){

	    $x1 = $node['cx'];
		$y1 = $node['cy'];
		$x2 = $node['bx'];
		$y2 = $node['by'];


		$line ='<polyline points="%d,%d,%d,%d" stroke-width="6" stroke="%s" class="shadow"/>';

		if($x2 > 0 && $y2>0)
		    echo sprintf($line,$x1,$y1,$x2,$y2,$this->line_color)."\n";

	}
	function draw_cirle($node){

        $color = $node['class']=='finished'?'darkred':$this->circle_color;

		$x= $node['cx'];
		$y = $node['cy'];
		$r = $node['r'];
		$circle = '<circle cx="%d" cy="%d" r="%d" fill="%s" stroke="%s" stroke-width="5" class="shadow"/>';
		echo sprintf($circle, $x, $y,$r,$color,$this->line_color);



	}
	function draw_text($node){
		$r = $node['r'];
		$x = $node['cx']-$r ;
		$y = $node['cy']+$r +$r/2 ;

		$text = '<text x="%d" y="%d" r="%d" style="font-size:16px;font-weight:bold;color: #999999;width:100px;text-align: center;">%s</text>';
		echo sprintf($text, $x, $y,$r,substr($node['title'],0,15));
	}

	function draw_labels(){
		foreach ($this->data as $node){
			$this->draw_text($node);
		}
	}
	function draw_path(){
		foreach ($this->data as $node){
			$this->draw_line($node);
		}
		foreach ($this->data as $node){
			$this->draw_cirle($node);
		}
	}

	function draw_gPoint($node, $edit=false){


		$r = intval($node['r']);

		$slug = $node['slug'];
		$h = $r * 2;
		$x= intval($node['cx']-$r);
		$y = intval($node['cy']-$r);
		$ty = intval($node['cy']+$r);
		$title = $node['title'];
		$img = $node['img'];


		if($edit){
			$g_point = '<g id="%1$s">';
		}else{
			$g_point = '<g id="%1$s" style="display: none;">';
		}
		$g_point .= '<a onclick="learnpath.show_quest(\'%1$s\')">';
		$g_point .= '<image x="%3$d" y="%4$d" max-height="100%%"  width="%2$d" clip-path="url(#clip_%1$s)" xlink:href="%5$s"></image>';
		$g_point .='</g>';


		echo sprintf($g_point, $slug,$h,$x, $y,$img)."\n";

    }

    function draw_gPoints($edit=false){
	    foreach ($this->data as $node){

	        $this->draw_gPoint($node,$edit);

        }
    }
    function draw_svg($edit = false){

	    $id = $this->id;
	    $vw = $this->vw;
	    $vh = $this->vh;

	    if($edit) $vh += 30;

        if( !$this->is_custom_bg){

            $left = $this->bg_left;
	        $top = $this->bg_top;

	        $bgstyle = "background-size: 265%;background-position: -{$left}px -{$top}px; ";

        }else{
	        $bgstyle = "background-size: cover;background-position: center center;background-blend-mode: overlay; background-color: {$this->bg_color}";
        }

        $bg = "background:url('{$this->background_img}');{$bgstyle};background-blend-mode: overlay; background-color: {$this->bg_color};";


	    $svg = '<style>.shadow{-webkit-filter: drop-shadow( 3px 3px 2px rgba(0, 0, 0, .7)); filter: drop-shadow( 3px 3px 2px rgba(0, 0, 0, .7))};</style>';
	    $svg .= '<svg id="%s" width="100%%" height="100%%" viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg" style="%s">';
	    echo sprintf($svg,$id,$vw,$vh,$bg);
	    $this->draw_defs();
	    $this->draw_path();
	    if ($edit) $this->draw_labels();
	    $this->draw_gPoints($edit);
	    echo '</svg>';

    }

    function draw_infobox(){
	    $id = $this->id;
	    ?>
        <style>
            .lp-infobox{
                min-width: 350px;
                max-width: 600px;
                float: right;
                border: 3px solid #bbb;
                border-radius: 15px;
                background: #999999;
                box-shadow: 7px 7px 8px 3px rgba(0, 0, 0, 0.2);
                position: absolute;
                right: 50px;
                top: -100000px;
                min-height: 150px;
                display: none;
                z-index: 1;
            }
            .lp-infobox-inner{
                font-size: 1.0rem;
                margin: 20px;

            }
            .lp-infobox h2{
                font-size: 1.3rem;
            }
            .lp-infobox input {
                font-size: 1.3rem;
                padding: 6px 20px;
                border-radius: 6px;
                width: 95%;
                background-color: transparent;
                border: 3px dashed #777;
                margin-top: 10px;
            }
            .lp-infobox input[type='submit'] {
                color:black; background-color:rgba(0,0,0,0.2); width:110px; border:0; float: right; margin: 30px;
            }
            .lp-infobox-inner form{
                padding-top: 20px;
                margin-top: 30px;

            }
            .lp-infobox .lp-infobox-button {
                float: right;
                margin-top:-3px;
                margin-right: 0px;
                font-size: xx-large;
                background-color: transparent;
                padding: 0px 20px 5px 20px;

            }
            .lp-infobox .lp-infobox-button:hover{
                cursor: pointer;
                border: 0px solid #777777;
                background: rgba(0, 0, 0, 0.1);
                border-radius: 50%;
            }
        </style>
        <div class="lp-infobox" style="background: url(<?php echo $this->base_url;?>/quest.jpg); background-position: -50px -50px;background-repeat:  no-repeat;">
            <div class="lp-infobox-button" onclick="learnpath.hide_quest()">x</div>
	        <div class="lp-infobox-inner">
                <h2 id="quest_title_<?php echo $id;?>"></h2>
                <div id="quest_<?php echo $id;?>"></div>
                <form id="quest_form_<?php echo $id;?>" onsubmit="learnpath.check_answer('<?php echo $id;?>'); return false;" style="display: none;">
                    <label for="lp_answer_<?php echo $id;?>"><?php echo $this->codeword_label;?>:
                        <input id="lp_answer_<?php echo $id;?>">
                    </label>
                    <input type="submit" value="Weiter" id="lp_button_<?php echo $id;?>" style="" />
                </form>

                <div style="display: none;" id="quest_form_status_<?php echo $id;?>" onclick="learnpath.copy_status('status_<?php echo $id;?>')">Wegmarke in die <u>Zwischenablage</u> kopieren.<input type="hidden" id="status_<?php echo $id;?>"/></div>
            </div>
        </div>
        <script>




        </script>
	    <?php
    }

    function draw_scripts(){
	    ?>

        <script>
            learnpath.id= '<?php echo $this->id;?>';
            learnpath.post_id = '<?php echo get_the_ID();?>';
            learnpath.data= <?php echo json_encode($this->data);?>;
            learnpath.slugs= <?php echo json_encode($this->ordered_slugs);?>;
            learnpath.config= <?php echo json_encode($this->config);?>;
            learnpath.vw =  <?php echo $this->vw;?>;
            learnpath.info_box_offset =  <?php echo $this->info_box_offset;?>;
            window.onhashchange = learnpath.check_active_hashtag;
            window.onresize = learnpath.adjust_info_box;
            learnpath.check_active_hashtag();
            learnpath.adjust_info_box();
            learnpath.is_mobile =<?php echo wp_is_mobile()?'true':'false';?>;
            learnpath.is_strict =<?php echo ($this->config['strict'] === true)?'true':'false';?>;
            let hash = sessionStorage.getItem('learnpath_hash_'+learnpath.post_id);
            if(hash){
                location.hash = hash;
            }
        </script>
		<?php

	}
}
