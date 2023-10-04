/*
 * 
 * NB: Sometimes the server gives an 1607 Cannot create stored procedure, check warnings...
 * 		--> to fix, add SET sql_mode=''; as in :
 * SET sql_mode='';
 * DELIMITER //
 * CREATE FUNCTION ...
	RETURNS varchar(500)
	LANGUAGE SQL
	NOT DETERMINISTIC
	NO SQL
	SQL SECURITY DEFINER
	COMMENT ''
 * BEGIN
 * 
 */


CREATE PROCEDURE `defineGlobalVars`()
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	SET @SESSION_ADMIN_USERID = "000";
	SET @ROLE_BYPASS_USER_PRODUCT_RESTRICTION = "95";
END


CREATE FUNCTION `hasRole`(`p_userId` INT, `p_entityId` INT, `p_roleId` INT)
	RETURNS tinyint(4)
	LANGUAGE SQL
	NOT DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
		DECLARE v_hasRole VARCHAR(1) DEFAULT 'N';
		DECLARE no_more_rows BOOLEAN;
		
		                                   
  		DECLARE CONTINUE HANDLER FOR NOT FOUND
    	SET no_more_rows = TRUE;
	
		                                             
		CALL defineGlobalVars();
		IF p_userId = @SESSION_ADMIN_USERID THEN 
			RETURN true; 
		END IF;
		
      select distinct 'Y'
      into   v_hasRole
		from   user_role a
		where  a.role_id = p_roleId
		and    a.user_id = p_userId
		and    a.entity_uid = p_entityId;
		
		                                         
		IF v_hasRole != 'Y' THEN
			select distinct 'Y'
			into   v_hasRole
			from   user_role a
			where  a.role_id = p_roleId
			and    a.user_id = p_userId
			and    a.entity_uid is null;
		END IF;
		
		IF v_hasRole = 'Y' THEN RETURN true;
		ELSE RETURN false;
		END IF;
       
END



drop function `alphaNumericValue`;

CREATE FUNCTION `alphaNumericValue`(`p_value` VARCHAR(500))
	RETURNS VARCHAR(500)
	LANGUAGE SQL
	NOT DETERMINISTIC
	NO SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
		DECLARE v_retVal VARCHAR(500) DEFAULT '';
		DECLARE v_copyVal VARCHAR(500) DEFAULT '';
		DECLARE v_len INT DEFAULT 0;
		DECLARE v_cnt INT DEFAULT 0;
		DECLARE v_char CHAR DEFAULT '';
		SET v_len = LENGTH(p_value);
    	SET v_copyVal = LOWER(p_value);
    
    	IF v_len != 0 THEN
	    	WHILE v_cnt <= v_len DO
	    	  SET v_char = SUBSTRING(v_copyVal,v_cnt,1);
	    	  IF (ORD(v_char) BETWEEN 97 AND 122) OR
				  (ORD(v_char) BETWEEN 48 AND 57) THEN
	    	    SET v_retVal=CONCAT(v_retVal,v_char);
	    	  END IF;
	    	  SET v_cnt=v_cnt+1;
	    	END WHILE;
    	END IF;
    
      RETURN v_retVal;
       
END;


delimiter $$
CREATE FUNCTION `rownum`(`p_thisuid` INT)
	RETURNS tinyint(4)
	LANGUAGE SQL
	NOT DETERMINISTIC
	READS SQL DATA
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	
		# Declare 'handlers' for exceptions
  		DECLARE CONTINUE HANDLER FOR NOT FOUND
    	
    	IF @rownum=0 THEN
    		SET @lastuid = -1;
    	END IF;
    	
		IF p_thisuid != @lastuid THEN
		  SET @lastuid = p_thisuid;
		  SET @rownum = 1;
		  RETURN 1;
		ELSE
			SET @rownum = @rownum+1;
			return @rownum;
		END IF;
       
END;
$$