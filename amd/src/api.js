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
 * @copyright   2023 iSpring Solutions Inc.
 * @author      Desktop Team <desktop-team@ispring.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call} from 'core/ajax';

const INVALID_PLAYER_ID_CODE = 'invalidplayerid';

class Api {
    constructor(sessionId, contentId, returnUrl, boxErrorId, iframeId) {
        this._sessionId = sessionId;
        this._contentId = contentId;
        this._returnUrl = returnUrl;
        this._boxErrorId = boxErrorId;
        this._iframeId = iframeId;
    }

    startSession(state) {
        call([{
            methodname: 'mod_ispring_start_session',
            args: {
                'content_id': this._contentId,
                'state': JSON.stringify(state)
            }
        }])[0]
            .then((result) => {
                this._sessionId = result['session_id'];
            })
            .catch(() => {
            });
    }

    setState(state) {
        call([{
            methodname: 'mod_ispring_set_state',
            args: {
                'session_id': this._sessionId,
                'state': JSON.stringify(state)
            }
        }])[0]
            .then((response) => {
                showErrorBoxIfNeeded(response, this._boxErrorId, this._iframeId);
            })
            .catch(() => {
            });
    }

    endSession(state) {
        call([{
            methodname: 'mod_ispring_end_session',
            args: {
                'session_id': this._sessionId,
                'state': JSON.stringify(state)
            }
        }])[0]
            .then((response) => {
                showErrorBoxIfNeeded(response, this._boxErrorId, this._iframeId);
            })
            .catch(() => {
            });
    }

    terminate() {
        window.location.replace(this._returnUrl);
    }
}

/**
 * @param {array} response
 * @param {string} boxId
 * @param {string} iframeId
 */
function showErrorBoxIfNeeded(response, boxId, iframeId) {
    if ('warning' in response && response['warning'].length > 0)
    {
        const warning = response['warning'][0];
        if (warning['warningcode'] === INVALID_PLAYER_ID_CODE)
        {
            document.getElementById(boxId).style.display = 'block';
            document.getElementById(boxId).innerHTML = warning['message'];
            document.getElementById(iframeId).parentElement.style.display = 'none';
        }
    }
}

/**
 * @param {string|null} persistStateId
 * @param {string|null} persistState
 */
function setPlayerData(persistStateId, persistState) {
    if (localStorage && persistStateId) {
        if (persistState) {
            localStorage.setItem(persistStateId, persistState);
        } else {
            localStorage.removeItem(persistStateId);
        }
    }
}

export const init = (contentId, playerUrl, iframeId, returnUrl, preloaderId, errorBoxId) => {
    window['ispring_moodle_connector'] = new Api(0, contentId, returnUrl, errorBoxId, iframeId);
    call([{
        methodname: 'mod_ispring_get_player_data',
        args: {
            'content_id': contentId
        }
    }])[0]
        .then((result) => {
            setPlayerData(result['persist_state_id'], result['persist_state']);
        })
        .catch(() /*noexcept*/ => {
        })
        .then(() => {
            document.getElementById(iframeId).src = playerUrl;
            document.getElementById(preloaderId).remove();
        });
};