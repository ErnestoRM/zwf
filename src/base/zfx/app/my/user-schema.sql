# Zerfrex(tm) Web Framework.
# Copyright (c) Jorge A. Montes Pérez <jorge@zerfrex.com>
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
# 3. Neither the name of copyright holders nor the names of its
#    contributors may be used to endorse or promote products derived
#    from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
# ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
# TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
# PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
# CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
# SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
# INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
# ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.


# Drops

DROP TABLE IF EXISTS zfx_userattribute CASCADE;
DROP TABLE IF EXISTS zfx_user_group CASCADE;
DROP TABLE IF EXISTS zfx_group_permission CASCADE;
DROP TABLE IF EXISTS zfx_user CASCADE;
DROP TABLE IF EXISTS zfx_group CASCADE;
DROP TABLE IF EXISTS zfx_permission CASCADE;


# Table and sequence definitions

CREATE TABLE IF NOT EXISTS zfx_group (
id bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS zfx_group_permission (
  id_group bigint(20) unsigned NOT NULL,
  id_permission bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS zfx_permission (
id bigint(20) unsigned NOT NULL,
  `code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS zfx_user (
id bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  email varchar(200) NOT NULL,
  password_hash char(32) NOT NULL,
  `language` char(2) NOT NULL,
  mobile varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS zfx_userattribute (
  id_user bigint(20) unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `value` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS zfx_user_group (
  id_user bigint(20) unsigned NOT NULL,
  id_group bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Constraints

ALTER TABLE zfx_group ADD PRIMARY KEY (id);
ALTER TABLE zfx_group_permission ADD PRIMARY KEY (id_group,id_permission), ADD KEY permission (id_permission);
ALTER TABLE zfx_permission ADD PRIMARY KEY (id), ADD UNIQUE KEY `code` (`code`);
ALTER TABLE zfx_user ADD PRIMARY KEY (id), ADD UNIQUE KEY email (email), ADD UNIQUE KEY `name` (`name`);
ALTER TABLE zfx_userattribute ADD PRIMARY KEY (id_user,`code`);
ALTER TABLE zfx_user_group ADD PRIMARY KEY (id_user,id_group), ADD KEY `group` (id_group);

ALTER TABLE zfx_group MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE zfx_permission MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE zfx_user MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE zfx_group_permission ADD CONSTRAINT zfx_group_permission_relPermission FOREIGN KEY (id_permission) REFERENCES zfx_permission (id) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT zfx_group_permission_relGroup FOREIGN KEY (id_group) REFERENCES zfx_group (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE zfx_userattribute ADD CONSTRAINT zfx_userattribute_relUser FOREIGN KEY (id_user) REFERENCES zfx_user (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE zfx_user_group ADD CONSTRAINT zfx_user_group_relUser FOREIGN KEY (id_user) REFERENCES zfx_user (id) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT zfx_user_group_relGroup FOREIGN KEY (id_group) REFERENCES zfx_group (id) ON DELETE CASCADE ON UPDATE CASCADE;

