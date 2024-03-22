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

final class create_or_update_ispring_module_use_case {
    private ispring_module_api_interface $ispringmoduleapi;
    private content_api_interface $contentapi;

    public function __construct(
        ispring_module_api_interface $ispringmoduleapi,
        content_api_interface $contentapi
    ) {
        $this->ispringmoduleapi = $ispringmoduleapi;
        $this->contentapi = $contentapi;
    }

    public function create(\stdClass $newispring, int $modulecontextid, int $usercontextid): int {
        $transaction = new db_transaction();
        try {
            $ispringmoduleid = $transaction->execute(
                fn() => $this->ispringmoduleapi->create(new create_or_update_ispring_module_input(
                    $newispring->name,
                    $newispring->course,
                    $newispring->grademethod,
                    new description_input(
                        $newispring->intro,
                        $newispring->introformat,
                    ),
                    $newispring->timeopen,
                    $newispring->timeclose,
                )),
                fn(int $id) => $this->ispringmoduleapi->delete($id),
            );

            $transaction->execute(
                fn() => $this->contentapi->add_content(new content_input(
                    $newispring->userfile,
                    $ispringmoduleid,
                    $modulecontextid,
                    $usercontextid,
                )),
                fn(int $id) => $this->contentapi->remove($modulecontextid, $id),
            );

            $transaction->commit();
            return $ispringmoduleid;
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    public function update(\stdClass $newispring, int $modulecontextid, int $usercontextid): bool {
        $transaction = new db_transaction();
        try {
            $this->ispringmoduleapi->update(
                $newispring->instance,
                new create_or_update_ispring_module_input(
                    $newispring->name,
                    $newispring->course,
                    $newispring->grademethod,
                    new description_input(
                        $newispring->intro,
                        $newispring->introformat,
                    ),
                    $newispring->timeopen,
                    $newispring->timeclose,
                ),
            );

            $transaction->execute(
                fn() => $this->contentapi->add_content(new content_input(
                    $newispring->userfile,
                    $newispring->instance,
                    $modulecontextid,
                    $usercontextid,
                )),
                fn(int $id) => $this->contentapi->remove($modulecontextid, $id),
            );

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }
}
