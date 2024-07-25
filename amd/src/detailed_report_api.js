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
 * @class       mod_ispring/api
 * @copyright   2024 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call} from 'core/ajax';

class Api {
    constructor(reportData, languageCode, returnUrl, userName, userImageUrl) {
        this._reportData = reportData;
        this._languageCode = languageCode;
        this._returnUrl = returnUrl;
        this._userName = userName;
        this._userImageUrl = userImageUrl;
    }

    state() {
        return this._reportData;
    }

    currentLanguage() {
        return this._languageCode;
    }

    /**
     * @return {string}
     */
    getUserName() {
        return this._userName;
    }

    /**
     * @return {string}
     */
    getUserImage() {
        return this._userImageUrl;
    }

    goBack() {
        window.location.replace(this._returnUrl);
    }
}

export const init = (
    sessionId,
    languageCode,
    iframeId,
    reportUrl,
    preloaderId,
    returnUrl,
    userName,
    userImageUrl,
) => {
    call([{
        methodname: 'mod_ispring_get_report_data',
        args: {
            'session_id': sessionId
        }
    }])[0]
        .then((result) => {
            window['ispring_report_connector'] = new Api(
                result['report_data'],
                languageCode,
                returnUrl,
                userName,
                userImageUrl,
            );
            document.getElementById(iframeId).src = reportUrl;
            document.getElementById(preloaderId).remove();
        });
};