<?php
require_once "dao/{$table->getEntityName()}/{$identifierName}{table_entity_name table=$table}AbstractDto.php";

/**
 * This class extends {$identifierName}{$table->getEntityName()}AbstractDto
 * that will be generated by the Carcara engine every time.
 *
 * Here is the right place to customize the dto's properties, getters and
 * setters. Once the Carcara engine finds this file in the dao structure the
 * engine will keep it untouched.
 *
 * @package lib.dao.{$table->getEntityName()}
 * @author Carcara Modeller Tool Engine
 */
class {$identifierName}{$table->getEntityName()}Dto extends {$identifierName}{table_entity_name table=$table}AbstractDto
{

}
