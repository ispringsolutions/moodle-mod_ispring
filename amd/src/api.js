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

class Api {
    constructor(sessionId, contentId, returnUrl) {
        this._sessionId = sessionId;
        this._contentId = contentId;
        this._returnUrl = returnUrl;
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
            .then(() => {
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
            .then(() => {
            })
            .catch(() => {
            });
    }

    terminate() {
        window.location.replace(this._returnUrl);
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

export const init = (contentId, playerUrl, iframeId, returnUrl) => {
    window['ispring_moodle_connector'] = new Api(0, contentId, returnUrl);
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
        });
};