-- Copyright (C) 2025 Your Name <your@email.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <https://www.gnu.org/licenses/>.

--
-- Table structure for llx_agendalimit_group
-- Store per-group agenda limit settings
--

CREATE TABLE llx_agendalimit_group (
    rowid           integer AUTO_INCREMENT PRIMARY KEY,
    fk_usergroup    integer NOT NULL,
    limit_enabled   smallint DEFAULT 1,
    limit_days      integer DEFAULT 30,
    date_creation   datetime NOT NULL,
    tms             timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat   integer,
    fk_user_modif   integer
) ENGINE=innodb;
