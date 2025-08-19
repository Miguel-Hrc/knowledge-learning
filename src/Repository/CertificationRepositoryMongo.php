<?php

namespace App\Repository;

use App\Document\CertificationDocument;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * Repository class for managing CertificationDocument documents in MongoDB.
 *
 * This repository provides methods to fetch, save, and manipulate
 * CertificationDocument objects using Doctrine MongoDB ODM.
 */
class CertificationRepositoryMongo extends DocumentRepository
{
}