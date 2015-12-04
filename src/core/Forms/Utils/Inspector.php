<?php

namespace Hyla\Forms\Utils;
use Hyla\Logger\Logger;


/**
 * Class Inspector
 * @package Hyla\Forms\Utils
 */
class Inspector
{

	private static $error;

	/**
	 * Inspector constructor.
	 */
	public function __construct ()
	{
		self::$error = '';
	}

	/**
	 * @param mixed $value
	 * @param string $type
	 * @param mixed $constraint
	 * @return bool
	 */
	public static function checkData($value, $type, $constraint)
	{
		$inspector = new Inspector();
		$method = 'check' . $type;

		return $inspector->$method($value, $constraint);
	}

	/**
	 * @return string
	 */
	public static function getMsg()
	{
		return self::$error;
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, array $arguments)
	{
		Logger::log('Undefined method : ' . $method . 'called with : ' . var_export($arguments, true), Logger::INFO);
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkNotBlank($value, $constraint)
	{
		if($constraint) {
			if (false === $value || (empty($value) && '0' != $value)) {
	            self::$error = '<span>This value should not be blank. </span>';
	            return false;
	        }
		} 
		return true;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkBlank($value, $constraint)
	{
		if($constraint) {
			if ('' !== $value && null !== $value) {
	            self::$error = '<span>This value should be blank. </span>';
	            return false;
	        }
		} 
		return true;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkNotNull($value, $constraint)
	{
		if($constraint) {
			if (null === $value) {
	            self::$error = '<span>This value should not be null. </span>';
	            return false;
	        }
		} 
		return true;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkNull($value, $constraint)
	{
		if($constraint) {
			if (null !== $value) {
	            self::$error = '<span>This value should be null. </span>';
	            return false;
	        }
		} 
		return true;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkTrue($value, $constraint)
	{
		if($constraint) {
			if (true !== $value && 1 !== $value && '1' !== $value) {
	            self::$error = '<span>This value should be true. </span>';
	            return false;
	        }
		} 
		return true;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkFalse($value, $constraint)
	{
		if($constraint) {
			if (null === $value || false === $value || 0 === $value || '0' === $value) {
	            return true;
	        } else {
	            self::$error = '<span>This value should be false. </span>';
	            return false;
	        }
		} 
		return true;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkType($value, $constraint)
	{
			if (null === $value) {
	            return true;
	        }

	        $type = strtolower($constraint);
	        $type = $type == 'boolean' ? 'bool' : $constraint;
	        $isFunction = 'is_' . $type;
	        $ctypeFunction = 'ctype_' . $type;

	        if (function_exists($isFunction) && call_user_func($isFunction, $value)) {
	            return true;
	        } elseif (function_exists($ctypeFunction) && call_user_func($ctypeFunction, $value)) {
	            return true;
	        } elseif ($value instanceof $constraint->type) {
	            return true;
	        }

	        self::$error = "<span>This value should be of type $constraint. </span>";
	        return false;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkEmail($value, $constraint)
	{
		if($constraint) {
			if (null === $value || '' === $value) {
	            return true;
	        }

	        $value = (string) $value;
	        $valid = filter_var($value, FILTER_VALIDATE_EMAIL);

	        if (!$valid) {
	            self::$error = '<span>This value is not a valid email address. </span>';
		        return false;
	        }
	    } 
		return true;    
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkLength($value, $constraint)
	{
		if (null === $value || '' === $value) {
            return true;
        }

        $stringValue = (string) $value;
        $length = strlen($stringValue);
        if ($constraint['min'] == $constraint['max'] && $length != $constraint['min']) {
            self::$error = "<span>This value should have exactly {$constraint['min']} characters... </span>";
	        return false;
        }
        if (null !== $constraint['max'] && $length > $constraint['max']) {
            self::$error = "<span>This value is too long. It should have {$constraint['max']} characters or less... </span>";
	        return false;
        }
        if (null !== $constraint['min'] && $length < $constraint['min']) {
           self::$error = "<span>This value is too short. It should have {$constraint['min']} characters or more... </span>";
	        return false;
        }

		return true;
	}

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkUrl($value, $constraint)
	{
		if($constraint) {
			if (null === $value || '' === $value) {
	            return true;
	        }

	        $value = (string) $value;
	        $valid = filter_var($value, FILTER_VALIDATE_URL);

	        if (!$valid) {
	            self::$error = '<span>This value is not a valid url. </span>';
		        return false;
	        }
	    } 
		return true; 
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkRegex($value, $constraint)
	{
		if (null === $value || '' === $value) {
            return true;
        }

        $value = (string) $value;
        if ($constraint['match']) {
        	if(!preg_match($constraint['pattern'], $value)) {
	            self::$error = '<span>This value is not valid. </span>';
			    return false;
			}
        } else{
			if(preg_match($constraint['pattern'], $value)) {
	            self::$error = '<span>This value is not valid. </span>';
			    return false;
			}
        } 
        return true;
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkIp($value, $constraint)
	{
		if (null === $value || '' === $value) {
            return true;
        }

        $value = (string) $value;

        switch ($constraint['version']) {
            case 'V4':
               $flag = FILTER_FLAG_IPV4;
               break;

            case 'V6':
               $flag = FILTER_FLAG_IPV6;
               break;

            case 'V4_NO_PRIV':
               $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE;
               break;

            case 'V6_NO_PRIV':
               $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE;
               break;

            case 'ALL_NO_PRIV':
               $flag = FILTER_FLAG_NO_PRIV_RANGE;
               break;

            case 'V4_NO_RES':
               $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE;
               break;

            case 'V6_NO_RES':
               $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_RES_RANGE;
               break;

            case 'ALL_NO_RES':
               $flag = FILTER_FLAG_NO_RES_RANGE;
               break;

            case 'V4_ONLY_PUBLIC':
               $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
               break;

            case 'V6_ONLY_PUBLIC':
               $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
               break;

            case 'ALL_ONLY_PUBLIC':
               $flag = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
               break;

            default:
                $flag = null;
                break;
        }

        if (!filter_var($value, FILTER_VALIDATE_IP, $flag)) {
            self::$error = '<span>This is not a valid IP address. </span>';
			return false;
        } else {
        	return true;
        }
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkRange($value, $constraint)
	{
		if (null === $value) {
            return true;
        }

        if (!is_numeric($value)) {
            self::$error = "<span>This value should be a valid number. </span>";
	        return false;
        }

        if (null !== $constraint['max'] && $value > $constraint['max']) {
            self::$error = "<span>This value should be {$constraint['max']} or less. </span>";
	        return false;
        }

        if (null !== $constraint->min && $value < $constraint->min) {
            self::$error = "<span>This value should be {$constraint['min']} or more. </span>";
	        return false;
        }
        return true;
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkEqualTo($value, $constraint)
	{
		foreach ($constraint as $compare) {
        	if($compare == $value) {
				return true;
			}
        }
        $constraintStr = implode(' or ', $constraint);
        self::$error = "<span>This value should be equal to $constraintStr. </span>";
        return false;
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
 	private function checkNotEqualTo($value, $constraint)
	{
		foreach ($constraint as $compare) {
        	if($compare == $value) {
				$constraintStr = implode(' or ', $constraint);
		        self::$error = "<span>This value should not be equal to $constraintStr. </span>";
		        return false;
        	} 
        }
        return true;
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkIdenticalTo($value, $constraint)
	{
		foreach ($constraint as $compare) {
        	if($compare === $value) {
				return true;
			}
        }
        $constraintStr = implode(' or ', $constraint);
        self::$error = "<span>This value should be identical to $constraintStr. </span>";
        return false;
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
 	private function checkNotIdenticalTo($value, $constraint)
	{
		foreach ($constraint as $compare) {
        	if($compare === $value) {
				$constraintStr = implode(' or ', $constraint);
		        self::$error = "<span>This value should not be identical to $constraintStr. </span>";
		        return false;
        	} 
        }
        return true;
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkLessThan($value, $constraint)
	{
		if($constraint > $value) {
			return true;
		} else {
        	self::$error = "<span>This value should be less than to $constraint. </span>";
        	return false;
        }
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkLessThanOrEqual($value, $constraint)
	{
		if($constraint >= $value) {
			return true;
		} else {
        	self::$error = "<span>This value should be less than or equal to $constraint. </span>";
        	return false;
        }
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkGreaterThan($value, $constraint)
	{
		if($constraint < $value) {
			return true;
		}
        else {
        	self::$error = "<span>This value should be greater than to $constraint. </span>";
        	return false;
        }
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkGreaterThanOrEqual($value, $constraint)
	{
		if($constraint <= $value) {
			return true;
		}
        else {
        	self::$error = "<span>This value should be greater than or equal to $constraint. </span>";
        	return false;
        }
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkDate($value, $constraint)
	{
		if($constraint) {
			if (null === $value || '' === $value || $value instanceof \DateTime) {
	            return true;
	        }

	        $value = (string) $value;

	        switch ($constraint['format']) {
	        	case 'fr':
	        		// jj-mm-aaaa
	        		$pattern = '#^(\d{2})-(\d{2})-(\d{4})$#';
	        		$order = [2,1,3];
	        		break;
	        	
	        	case 'en':
	        		// mm/jj/aaaa
	        		$pattern = '#^(\d{2})/(\d{2})/(\d{4})$#';
	        		$order = [1,2,3];
	        		break;
	        	
	        	default:
	        		// aaaa-mm-jj
	        		$pattern = '#^(\d{4})-(\d{2})-(\d{2})$#';
	        		$order = [2,3,1];
	        		break;
	        	}

	        if (!preg_match($pattern, $value, $matches) || !checkdate($matches[$order[0]], $matches[$order[1]], $matches[$order[2]])) {
	            self::$error = '<span>This value is not a valid date. </span>';
        		return false;
	        }

			return true;
		}
    }

	/**
	 * @param $value
	 * @param $constraint
	 * @return bool
	 */
	private function checkFile($value, $constraint)
	{
		// origin : http://www.php.net/manual/fr/features.file-upload.php#114004
		    // Undefined | Multiple Files | $_FILES Corruption Attack
		    // If this request falls under any of them, treat it invalid.
		    if (
		        !isset($value['error']) ||
		        is_array($value['error'])
		    ) {
		    	self::$error = '<span>Invalid parameters. </span>';
        		return false;
		    }

		    // Check $_FILES['upfile']['error'] value.
		    switch ($value['error']) {
		        case UPLOAD_ERR_OK:
		            break;
		        case UPLOAD_ERR_NO_FILE:
			        self::$error = '<span>No file sent. </span>';
	        		return false;
		        case UPLOAD_ERR_INI_SIZE:
		        case UPLOAD_ERR_FORM_SIZE:
			        self::$error = '<span>Exceeded filesize limit. </span>';
	        		return false;
		        default:
			        self::$error = '<span>Unknown errors. </span>';
	        		return false;
		    }

		    // You should also check filesize here.
		    if ($value['size'] > $constraint['maxSize']) {
		        self::$error = '<span>Exceeded filesize limit. </span>';
	        	return false;
		    }

		    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
		    // Check MIME Type by yourself.
		    $finfo = new \finfo(FILEINFO_MIME_TYPE);
		    $pattern = '/'.$constraint['mimeTypes'].'/';
		    if (0 === $ext = preg_match($pattern, $finfo->file($value['tmp_name']))) {
		        self::$error = '<span>Invalid file format. </span>';
	        	return false;
		    }

		    return true;
    }
}
