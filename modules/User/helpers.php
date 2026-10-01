<?php

use Modules\Blogcategory\Entities\Blogcategory;
use Modules\Newscategory\Entities\Newscategory;
use Illuminate\Support\Facades\URL;
use Modules\Media\Entities\File;
use Modules\Block\Entities\Block;
use Modules\Country\Entities\Country;
use Modules\TemplateCategory\Entities\TemplateCategory;
use Modules\BusinessCategory\Entities\BusinessCategory;
use Modules\Support\State;

if (!function_exists('permission_value')) {
    /**
     * Get the integer representation value of the permission.
     *
     * @param array $permissions
     * @param string $permission
     *
     * @return int
     */

    function _print($arr) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }
    
    function badgePositions()
    {
        $positions = [];
        $positionValues=[
            '' => "Please Select",
            1 => "Center",
            2 => "Right",
            3 => "Left",
        ];
        foreach ($positionValues as $key => $value) {
            $positions[$key] = $value;
        }
        return $positions;
    }

    function userCategory()
    {
        $category = [];
        $categoryValues=[
            '' => "Please Select",
            '1'=>"SC",
            '2'=>"ST",
            '3'=>"OBC",
            '4'=>"GENERAL",
            '5'=>"MINORITY",
        ];
        foreach ($categoryValues as $key => $value) {
            $category[$key] = $value;
        }
        return $category;
    }

    function userReligion()
    {
        $religion = [];
        $religionValues=[
            '' => "Please Select",
            '1'=>"HINDU",
            '2'=>"MUSLIM",
            '3'=>"BUDDHISTS",
            '4'=>"SIKHS",
            '5'=>"JAINS",
            '6'=>"CHRISTAINS",
        ];
        foreach ($religionValues as $key => $value) {
            $religion[$key] = $value;
        }
        return $religion;
    }

    function permission_value(array $permissions, $permission)
    {
        $value = array_get($permissions, $permission);

        if (is_null($value)) {
            return 0;
        } else if ($value) {
            return 1;
        } else if (!$value) {
            return -1;
        }
    }
    
    /*For creating Shortcode and convert HTML code */
    if (! function_exists('the_content')) {
        function the_content($str)
        {
            $a = strstr($str, '[ai:');
            if (! strstr($str, '[ai:')) {
                return $str;
            }

            preg_match_all('/\[ai:([\x{0600}-\x{06FF}a-zA-Z0-9-_: |=\/\.\&amp;\-]+)]/u', $str, $shortcodes);
            
            if ($shortcodes == NULL) {
                return $str;
            }
            
            foreach ($shortcodes[1] as $key => $shortcode) {
                if (strstr($shortcode, ' ')) {
                    $code = substr($shortcode, 0, strpos($shortcode, ' '));
                    $tmp = explode('|', str_replace($code . ' ', '', $shortcode));
                    $params = array();
                    if (count($tmp)) {
                        foreach ($tmp as $param) {
                            $pair = explode('=', $param);
                            $params[$pair[0]] = $pair[1];
                        }
                    }
                    $array = array('code' => $code, 'params' => $params);
                } else {
                    $array = array('code' => $shortcode, 'params' => array());
                }
                $shortcode_array[$shortcodes[0][$key]] = $array;
            }
            
            if (count($shortcode_array)) {         
                foreach ($shortcode_array as $search => $shortcode) {
                    if ($shortcode['code'] == 'anchor') {
                        $str = str_replace($search, run_html_anchor_decoder($shortcode['params']), $str);   
                    } elseif ($shortcode['code'] == 'img') {
                        $str = str_replace($search, run_html_img_decoder($shortcode['params']), $str);
                    } elseif($shortcode['code'] == 'block') {
                        $str = str_replace($search, run_html_block_decoder($shortcode['params']), $str);   
                    }
                }
            }

            return $str;
        }
    }

    /* Anchor tag code */
    if (! function_exists('run_html_anchor_decoder')) {
        function run_html_anchor_decoder($options = [])
        {
            $curUrl = URL::current();
            $url = URL::to('/');
            $page = false;
            if (strpos($curUrl, "/orthopass/public/") !== false) {
                $expUrl = explode("/orthopass/public/", $url);
                if (count($expUrl) > 1) {
                    $slugExp = explode('/', $expUrl[1]);
                    $pageSlug = $slugExp[0];
                    if ($pageSlug) {
                        $page = true;
                        $url = $expUrl[0]."/orthopass/public/".$pageSlug."/";
                    }
                }
            }
            $defaults = array(
               'title' => $options['text']
            );
            if (array_key_exists('class', $options) && $options['class'] == 'none') {
                $classVal = '';
            } else if (array_key_exists('class', $options) && $options['class'] != 'none') {
                $classVal = "class='".$options['class']."'";
            } else {
                $classVal = "class='btn btn-prime'";
            }
            if (array_key_exists('pcode', $options) && $options['pcode'] != '') {
                if ($page) {
                    $options['href'] = $url.$options['href']."?pcode=".$options['pcode'];
                } else {
                    $options['href'] = $url.'/'.$options['href']."?pcode=".$options['pcode'];
                }            
            } else {
                if ($page) {
                    $options['href'] = $url.$options['href'];
                } else {
                    $options['href'] = $url.'/'.$options['href'];
                }
            }
            $target = '';
            if (array_key_exists('target', $options) && $options['target'] != '') {
                $target = "target='_blank'";
            }
    
            $options = array_merge($defaults, $options);
            $anchor = '';
            if ($options != '') {
                $anchor = "<a href='".$options['href']."' title='".$options['title']."' ".$classVal." ".$target." >".$options['text']."</a>";
            }
            return $anchor;
        }
    }

    /* Image Short code */
    if (! function_exists('run_html_img_decoder')) {
        function run_html_img_decoder($options = [])
        {
            $defaults = array(
               'alt' => $options['text']
            );
            $options = array_merge($defaults, $options);
            if (!array_key_exists('media', $options) || (array_key_exists('media', $options) && $options['media'] == '')) {
                return '';
            }
    
            if (array_key_exists('class', $options) && $options['class'] == 'none') {
                $classVal = '';
            } else if (array_key_exists('class', $options) && $options['class'] != 'none') {
                $classVal = "class='".$options['class']."'";
            } else {
                $classVal = "class=' '";
            }
    
    
            $mediaId = $options['media']; 
            $image = File::where('id', $mediaId)->first();
            $img = '';
    
            if ($image) {            
                if ($options != '') {
                    $img = '<img src="'.$image->path.'" alt="'.$options['alt'].'" '.$classVal.'>';
                }
            }
            return $img;
        }
    }

    /* Block Short code */
    if (! function_exists('run_html_block_decoder')) {
        function run_html_block_decoder($options = [])
        {    
            $id = array(
                'id' => $options['id']
            );    
            $options = array_merge($id, $options);
            if (!array_key_exists('id', $options) || (array_key_exists('id', $options) && $options['id'] == '')) {
                return '';
            }    
            $identifierId = $options['id'];
            $blockData = Block::where('id', $identifierId)->where('is_active',1)->first();    
            if($blockData == ''){
                return '';
            }
            $blockData->getBlockTranslationById($identifierId);
            $blockHTML = '';
            if ($blockData) {            
                if ($options != '') {
                    $blockHTML = $blockData['content'];
                }
            }            
            return the_content($blockHTML);
        }
    }

    function templateCategory()
    {
        return TemplateCategory::where('is_active', 1)->orderBy('short_order', 'asc')->get();
    }

    function businessCategories()
    {
        return BusinessCategory::where('is_active', 1)->get();
        /* $businessType = [];
        $businessTypeValues=[            
            'restaurant'=>"Restaurant",
            'hotel'=>"Hotel",
            'beauty'=>"Beauty",
            'real_estate'=>"Real Estate",
            'doctor'=>"Doctor",
            'car'=>"Car",
        ];
        foreach ($businessTypeValues as $key => $value) {
            $businessType[$key] = $value;
        }
        return $businessType; */
    }

    function Categories()
    {
        $Categories = [];
        $CategoriesValues=[            
            ''=>"Please Select",
            'religious'=>"Religious",
            'political' => "Political",
            'social' => "Social",
            'profession' => "Profession",
            'educational' => "Educational",
            'business' => "Business",
            'entertainment' => "Entertainment",
            'ngo' => "NGO",
            'other' => "Other",
        ];
        foreach ($CategoriesValues as $key => $value) {
            $Categories[$key] = $value;
        }
        return $Categories;
    }

    function timeSlots()
    {
        $timeSlots = [];
        $timeSlotsValues = [
            "" => "Opening Time",
            "12_am" => "12 am",
            "01_am" => "01 am",
            "02_am" => "02 am",
            "03_am" => "03 am",
            "04_am" => "04 am",
            "05_am" => "05 am",
            "06_am" => "06 am",
            "07_am" => "07 am",
            "08_am" => "08 am",
            "09_am" => "09 am",
            "10_am" => "10 am",
            "11_am" => "11 am",
            "12_pm" => "12 pm",
            "01_pm" => "01 pm",
            "02_pm" => "02 pm",
            "03_pm" => "03 pm",
            "04_pm" => "04 pm",
            "05_pm" => "05 pm",
            "06_pm" => "06 pm",
            "07_pm" => "07 pm",
            "08_pm" => "08 pm",
            "09_pm" => "09 pm",
            "10_pm" => "10 pm",
            "11_pm" => "11 pm",
            "close" => "Closed",
        ];
        foreach ($timeSlotsValues as $key => $value) {
            $timeSlots[$key] = $value;
        }
        return $timeSlots;
    }
    
    function propertyType()
    {
        $propertyType = [];
        $propertyTypeValues=[
            '' => "Please select",
            'sell'=>"Sell",
            'rent'=>"Rent",
        ];
        foreach ($propertyTypeValues as $key => $value) {
            $propertyType[$key] = $value;
        }
        return $propertyType;
    }

    function states()
    {
        $states = State::get('IN');
        return $states;
    }

    function businessType()
    {
        $businessType = [];
        $businessTypeValues=[
            '' => "Please select",
            'restaurant'=>"Restaurant",
            'hotel'=>"Hotel",
            'beauty'=>"Beauty",
            'real_estate'=>"Real Estate",
            'doctor'=>"Doctor",
            'car'=>"Car",
        ];
        foreach ($businessTypeValues as $key => $value) {
            $businessType[$key] = $value;
        }
        return $businessType;
    }

    function education()
    {
        $education = [];
        $educationValues=[
            '' => "Please select",
            'under_10'=>"Under 10",
            '10th_pass'=>"10th pass",
            '12th_pass'=>"12th pass",
            'graduate'=>"Graduate",
            'masters_degree'=>"Master's Degree",
        ];
        foreach ($educationValues as $key => $value) {
            $education[$key] = $value;
        }
        return $education;
    }
}

