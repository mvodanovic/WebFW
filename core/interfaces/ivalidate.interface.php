<?php

namespace WebFW\Core\Interfaces;

interface iValidate
{
    function validateData();
    function addValidationError($field, $error);
    function hasValidationErrors();
    function getValidationErrors($field = null);
    function clearValidationErrors();
}
