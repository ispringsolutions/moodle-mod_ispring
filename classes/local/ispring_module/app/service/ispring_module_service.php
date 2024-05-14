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

namespace mod_ispring\local\ispring_module\app\service;

use mod_ispring\local\ispring_module\app\data\ispring_module_data;
use mod_ispring\local\ispring_module\app\repository\ispring_module_repository_interface;
use mod_ispring\local\ispring_module\domain\model\grading_options;

class ispring_module_service {
    private ispring_module_repository_interface $repository;

    public function __construct(
        ispring_module_repository_interface $repository
    ) {
        $this->repository = $repository;
    }

    public function create(ispring_module_data $data): int {
        if (!grading_options::is_grade_option($data->get_grade_method())) {
            throw new \RuntimeException("Wrong grade option");
        }
        return $this->repository->add($data);
    }

    public function update(int $id, ispring_module_data $data): bool {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool {
        return $this->repository->remove($id);
    }
}
