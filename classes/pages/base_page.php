<?php

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 *
 * @package     mod_ispring
 * @copyright   2024 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ispring\pages;

abstract class base_page
{
    /** @var mixed */
    private $page;
    /** @var mixed */
    private $output;

    public function __construct(string $url, array $args = null)
    {
        global $PAGE;
        global $OUTPUT;
        $this->page = $PAGE;
        $this->output = $OUTPUT;

        $this->page->set_url($url, $args);
    }

    public function get_header(): string
    {
        return $this->output->header();
    }

    public function get_footer(): string
    {
        return $this->output->footer();
    }

    public function set_title(string $title): void
    {
        $this->page->set_title($title);
    }

    public function set_heading(string $heading): void
    {
        $this->page->set_heading($heading);
    }

    public function set_context(\context_module $context_module): void
    {
        $this->page->set_context($context_module);
    }

    public function set_secondary_active_tab(string $name): void
    {
        $this->page->set_secondary_active_tab($name);
    }

    /**
     * Available page layouts for specific theme can be found in Moodle source code (/theme/…/config.php files)
     *
     * @param string $page_layout
     */
    public function set_page_layout(string $page_layout): void
    {
        $this->page->set_pagelayout($page_layout);
    }

    public function add_navbar(string $nav_name, string $url, array $args): void
    {
        $this->page->navbar->add($nav_name, new \moodle_url($url, $args));
    }

    abstract public function get_content(): string;

    /**
     * @return mixed
     */
    protected function get_page()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    protected function get_output()
    {
        return $this->output;
    }
}