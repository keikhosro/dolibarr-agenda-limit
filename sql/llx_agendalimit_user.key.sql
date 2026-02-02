-- Copyright (C) 2025 Your Name <your@email.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

ALTER TABLE llx_agendalimit_user ADD UNIQUE INDEX uk_agendalimit_user_fk_user (fk_user);
ALTER TABLE llx_agendalimit_user ADD CONSTRAINT fk_agendalimit_user_fk_user FOREIGN KEY (fk_user) REFERENCES llx_user(rowid);
