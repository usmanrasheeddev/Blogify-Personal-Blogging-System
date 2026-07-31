<?php
// Vercel serverless wrapper for index.php
// Sets the working directory to project root so all includes/paths resolve correctly
chdir(dirname(__DIR__));
require __DIR__ . '/../index.php';
