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

namespace local_customapi\exception;

use RuntimeException;
use Throwable;

class customapiException extends RuntimeException {

    private $warning;

    public function __construct($errorcode, $item = null, Throwable $previous = null) {
        if (empty($item)) {
            // Unexpected error.
            $this->warning = [
                'item' => null,
                'warningcode' => $previous->getCode() ?? 0,
                'message' => 'Unhandled error',
            ];
        } else {
            // Expected error.
            $this->warning = [
                // Convert item to a string, if it isn't already.
                'item' => is_string($item) ? $item : json_encode($item),
                'warningcode' => $errorcode,
                'message' => get_string($errorcode, 'local_customapi'),
            ];
        }

        if ($previous) {
            $this->warning['message'] .= ': ' . $previous->getMessage();
            // If there's extra debug info, include that too.
            if ($debuginfo = $previous->debuginfo ?? false) {
                $this->warning['message'] .= " ({$debuginfo})";
            }
        }

        parent::__construct($this->warning['message'], 0, $previous);
    }

    public function getwarning() {
        return $this->warning;
    }
}
