<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Antispam, a plugin for Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"Antispam",
	/* Description*/		"Generic antispam plugin for Dotclear",
	/* Author */			"Alain Vagner",
	/* Version */			'1.2',
	/* Permissions */		'usage,contentadmin',
	/* Priority */			10
);
?>