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

namespace mod_ispring\use_case;

use mod_ispring\common\infrastructure\transaction\db_transaction;
use mod_ispring\content\api\content_api_interface;
use mod_ispring\content\api\input\content_input;
use mod_ispring\ispring_module\api\input\create_or_update_ispring_module_input;
use mod_ispring\ispring_module\api\input\description_input;
use mod_ispring\ispring_module\api\ispring_module_api_interface;

final class create_or_update_ispring_module_use_case
{
    private ispring_module_api_interface $ispring_module_api;
    private content_api_interface $content_api;

    public function __construct(
        ispring_module_api_interface $ispring_module_api,
        content_api_interface $content_api
    )
    {
        $this->ispring_module_api = $ispring_module_api;
        $this->content_api = $content_api;
    }

    public function create(\stdClass $new_ispring, int $module_context_id, int $user_context_id): int
    {
        $transaction = new db_transaction();
        try
        {
            $ispring_module_id = $transaction->execute(
                fn() => $this->ispring_module_api->create(new create_or_update_ispring_module_input(
                    $new_ispring->name,
                    $new_ispring->course,
                    $new_ispring->grademethod,
                    new description_input(
                        $new_ispring->intro,
                        $new_ispring->introformat,
                    ),
                    $new_ispring->timeopen,
                    $new_ispring->timeclose,
                )),
                fn(int $id) => $this->ispring_module_api->delete($id),
            );

            $transaction->execute(
                fn() => $this->content_api->add_content(new content_input(
                    $new_ispring->userfile,
                    $ispring_module_id,
                    $module_context_id,
                    $user_context_id,
                )),
                fn(int $id) => $this->content_api->remove($module_context_id, $id),
            );

            $transaction->commit();
            return $ispring_module_id;
        }
        catch (\Throwable $e)
        {
            $transaction->rollback($e);
            throw $e;
        }
    }

    public function update(\stdClass $new_ispring, int $module_context_id, int $user_context_id): bool
    {
        $transaction = new db_transaction();
        try
        {
            $this->ispring_module_api->update(
                $new_ispring->instance,
                new create_or_update_ispring_module_input(
                    $new_ispring->name,
                    $new_ispring->course,
                    $new_ispring->grademethod,
                    new description_input(
                        $new_ispring->intro,
                        $new_ispring->introformat,
                    ),
                    $new_ispring->timeopen,
                    $new_ispring->timeclose,
                ),
            );

            $transaction->execute(
                fn() => $this->content_api->add_content(new content_input(
                    $new_ispring->userfile,
                    $new_ispring->instance,
                    $module_context_id,
                    $user_context_id,
                )),
                fn(int $id) => $this->content_api->remove($module_context_id, $id),
            );

            $transaction->commit();
            return true;
        }
        catch (\Throwable $e)
        {
            $transaction->rollback($e);
            throw $e;
        }
    }
}