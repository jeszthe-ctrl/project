<?php
/* The puppies (and their parents) a site starts with, from the owner's spreadsheet.
   Added once; after that they are edited in Admin → Puppies. */
if(!defined('BB_ROOT')){ http_response_code(404); exit; }

return ['puppies'=>[], 'parents'=>[]];
