<?php
namespace WebFW\Interfaces;

interface IDatabaseHandler
{
	public function escapeIdentifier($identifier);
	public function escapeLiteral($literal);
	public function query($query);
	public function fetchAssoc($queryResource = false, $row = null);
	public function fetchAll($queryResource = false);
	public function getAffectedRows($queryResource = false);
	public function getLimitAndOffset($limit, $offset = 0);
	public function convertBoolean($value);
}