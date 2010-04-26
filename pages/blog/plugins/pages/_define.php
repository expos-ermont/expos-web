<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		"Pages",
	/* Description*/	"Serve entries as simple web pages",
	/* Author */		"Olivier Meunier",
	/* Version */		'1.0',
	/* Permissions */	'contentadmin,pages',
	999
);
?>