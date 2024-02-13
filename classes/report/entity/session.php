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

namespace mod_ispring\report\entity;

use core_reportbuilder\local\filters\number as number_filter;
use core_reportbuilder\local\filters\text as text_filter;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;
use mod_ispring\report\entity\base as base_entity;
use mod_ispring\session\domain\model\session_state;

class session extends base_entity
{
    private string $page_url;

    public function __construct(
        string $page_url
    )
    {
        $this->page_url = $page_url;
    }

    protected function get_default_table_aliases(): array
    {
        return [
            'ispring_session' => 'iss',
        ];
    }

    protected function get_default_entity_title(): lang_string
    {
        return new lang_string('entitysession', 'ispring');
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     * @throws \coding_exception
     */
    protected function get_all_columns(): array
    {
        $alias = $this->get_table_alias('ispring_session');
        $columns = [];

        $columns[] = (new column(
            'review',
            new lang_string('reviewresult', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.id")
            ->add_callback(function(string $session_id): string {
                $link = $this->get_detailed_report_link($session_id);
                return \html_writer::link($link->out(false), get_string('reviewattempt', 'ispring'));
            });

        $columns[] = (new column(
            'status',
            new lang_string('status', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("{$alias}.status")
            ->add_callback(static function(string $status): string {
                switch ($status)
                {
                    case session_state::INCOMPLETE: return get_string('statusinprogress', 'ispring');
                    case session_state::COMPLETE: return get_string('statuscomplete', 'ispring');
                    case session_state::PASSED: return get_string('statuspassed', 'ispring');
                    case session_state::FAILED: return get_string('statusfailed', 'ispring');
                    default: return get_string('statusunknown', 'ispring');
                }
            });

        $columns[] = (new column(
            'attempt',
            new lang_string('attempt', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.attempt")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'score',
            new lang_string('score', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.score")
            ->set_is_sortable(true);

        $columns[] = (new column(
            'max_score',
            new lang_string('maxscore', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.max_score");

        $columns[] = (new column(
            'end_time',
            new lang_string('endtime', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.end_time")
            ->add_callback(static function(?int $end_time): string {
                if ($end_time === null)
                {
                    return '-';
                }
                return userdate($end_time, get_string('strftimedatetime', 'langconfig'));
            })
            ->set_is_sortable(true);

        $columns[] = (new column(
            'begin_time',
            new lang_string('begintime', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.begin_time")
            ->add_callback(static function(int $begin_time): string {
                return userdate($begin_time, get_string('strftimedatetime', 'langconfig'));
            })
            ->set_is_sortable(true);

        $columns[] = (new column(
            'duration',
            new lang_string('duration', 'ispring'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.duration")
            ->add_callback(static function(?int $duration): string {
                return self::format_duration($duration);
            })
            ->set_is_sortable(true);

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_all_filters(): array
    {
        $alias = $this->get_table_alias('ispring_session');

        $filters[] = (new filter(
            text_filter::class,
            'status',
            new lang_string('status', 'ispring'),
            $this->get_entity_name(),
            "{$alias}.status"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number_filter::class,
            'attempt',
            new lang_string('attempt', 'ispring'),
            $this->get_entity_name(),
            "{$alias}.attempt"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number_filter::class,
            'score',
            new lang_string('score', 'ispring'),
            $this->get_entity_name(),
            "{$alias}.score"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number_filter::class,
            'begin_time',
            new lang_string('begintime', 'ispring'),
            $this->get_entity_name(),
            "{$alias}.begin_time"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number_filter::class,
            'end_time',
            new lang_string('endtime', 'ispring'),
            $this->get_entity_name(),
            "{$alias}.end_time"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            number_filter::class,
            'duration',
            new lang_string('duration', 'ispring'),
            $this->get_entity_name(),
            "{$alias}.duration"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

    private static function format_duration(?int $duration): string
    {
        return $duration ? format_time($duration) : '-';
    }

    private function get_detailed_report_link(string $session_id): \moodle_url
    {
        $args = ['session_id' => $session_id];
        if ($this->page_url)
        {
            $args['return_url'] = $this->page_url;
        }
        return new \moodle_url('/mod/ispring/detailed_report.php', $args);
    }
}