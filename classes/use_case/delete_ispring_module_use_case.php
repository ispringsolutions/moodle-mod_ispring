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
 * @copyright   2023 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ispring\use_case;

use mod_ispring\common\infrastructure\transaction\db_transaction;
use mod_ispring\di_container;
use mod_ispring\ispring_module\api\ispring_module_api_interface;
use mod_ispring\ispring_module\api\output\ispring_module_output;

class delete_ispring_module_use_case
{
    public function __construct(
        private readonly ispring_module_api_interface $ispring_module_api,
        private readonly ispring_module_output $module
    )
    {
    }

    public function delete(): bool
    {
        $transaction = new db_transaction();
        try
        {
            $content_api = di_container::get_content_api();
            $session_api = di_container::get_session_api();

            $ids = $content_api->get_ids_by_ispring_module_id($this->module->get_id());

            [, $cm] = get_course_and_cm_from_instance($this->module->get_id(), 'ispring');
            $context = \context_module::instance($cm->id);

            foreach ($ids as $id)
            {
                $content_api->remove($context->id, $id);
                $session_api->delete_by_content_id($id);
            }
            $result = $this->ispring_module_api->delete($this->module->get_id());

            $transaction->commit();
            return $result;
        } catch (\Throwable $e)
        {
            $transaction->rollback($e);
            return false;
        }
    }
}