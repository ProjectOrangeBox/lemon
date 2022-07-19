<?php

(new \dmyers\orange\Container)->output->contentType(\dmyers\orange\Output::JSON);

echo json_encode($json, \dmyers\orange\Output::JSONOPTIONS);
