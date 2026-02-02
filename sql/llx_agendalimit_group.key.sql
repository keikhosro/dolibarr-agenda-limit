-- Copyright (C) 2025 Your Name <your@email.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

ALTER TABLE llx_agendalimit_group ADD UNIQUE INDEX uk_agendalimit_group_fk_usergroup (fk_usergroup);
ALTER TABLE llx_agendalimit_group ADD CONSTRAINT fk_agendalimit_group_fk_usergroup FOREIGN KEY (fk_usergroup) REFERENCES llx_usergroup(rowid);
