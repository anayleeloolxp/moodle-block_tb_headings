<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Content Box block
 *
 * @package    block_tb_headings
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This block simply outputs the Headings.
 *
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_tb_headings extends block_base {

    /**
     * Initialize.
     *
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_tb_headings');
    }

    /**
     * Return contents of tb_headings block
     *
     * @return stdClass contents of block
     */
    public function get_content() {

        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $leeloolxplicense = get_config('block_tb_headings')->license;

        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_tb_headings');
            return $this->content;
        }

        $infoleeloolxp = json_decode($output);

        if ($infoleeloolxp->status != 'false') {
            $leeloolxpurl = $infoleeloolxp->data->install_url;
        } else {
            $this->content->text = get_string('nolicense', 'block_tb_headings');
            return $this->content;
        }

        $url = $leeloolxpurl . '/admin/Theme_setup/get_heading_settings';

        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_tb_headings');
            return $this->content;
        }

        $resposedata = json_decode($output);
        $mdata = $resposedata->data->heading_settings;

        if (empty($resposedata->data->block_title)) {
            $resposedata->data->block_title = get_string('displayname', 'block_tb_headings');
        }
        $this->title = $resposedata->data->block_title;
        $autoslide = $resposedata->data->autoslide;

        $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/tb_headings/js/jquery.min.js'));
        $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/tb_headings/js/owl.carousel.js'));
        if($autoslide == 1){
            $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/tb_headings/js/owlslider-auto.js'));
        }else{
            $this->page->requires->js(new moodle_url($CFG->wwwroot . '/blocks/tb_headings/js/owlslider.js'));
        }

        $this->page->requires->css(new moodle_url($CFG->wwwroot . '/blocks/tb_headings/css/owl.carousel.min.css'));
        $this->page->requires->css(new moodle_url($CFG->wwwroot . '/blocks/tb_headings/css/owl.theme.default.min.css'));

        $this->content = new stdClass();
        $this->content->text = '<div class="tb_headings owl-carousel owl-theme">';

        foreach($mdata as $mdatasing){
            $this->content->text .= '<div id="hd_box" class="hd_box">';

            $this->content->text .= '<div class="hd_img">';
            $this->content->text .= '<img src="' . $mdatasing->image . '"/>';
            $this->content->text .= '</div>';
    
            $this->content->text .= '<div class="hd_title">';
            $this->content->text .= $mdatasing->setting_title;
            $this->content->text .= '</div>';
    
            $this->content->text .= '<div class="hd_des">';
            $this->content->text .= $mdatasing->description;
            $this->content->text .= '</div>';

            $this->content->text .= '<div class="hd_link">';
            $this->content->text .= '<a href="'.$mdatasing->button_link.'">';
            $this->content->text .= $mdatasing->button;
            $this->content->text .= '</a>';
            $this->content->text .= '</div>';
    
            $this->content->text .= '</div>';
        }

        $this->content->text .= '</div>';

        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }
}
