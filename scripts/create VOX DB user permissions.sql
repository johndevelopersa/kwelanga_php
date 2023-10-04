-- !! NB
-- Do not set any flags to Y in mysql.db as those are global and will continue to override lower level permissions ragardless of what u do below !!

-- Also, if you are already logged into HeidiSQL a(the use database command has been issued and accepted already) then the user will still see that database and tables !


/*****************************************************************************************************
*  Hierarchy :
*	mysql.db ~ global permissions only, user is only in here if using *.*
*  mysql.tables_priv ~ tables
*  mysql.columns_priv ~ columns
*  mysql.proc_priv ~ procedures
******************************************************************************************************/ 



/*****************************************************************************************************
*  user_alan
******************************************************************************************************/ 

revoke all privileges, grant option from 'usr_alan'@'%'; -- do not use "on *.*" as that wont remove DB global privileges ; syntax err if u leave out grant option
revoke usage on *.* from 'usr_alan'@'%';

grant usage on retailtr_rttlive.* to 'usr_alan'@'%';

flush privileges;

/*****************************************************************************************************
*  Add SELECT table permissions specifically
*	We do it this way because if you use *.* then you cant revoke access on a specific table / col later
******************************************************************************************************/ 
-- RUN THIS 2ndary SQL MAMNUALLY !!
select concat('grant usage, select on retailtr_rttlive.',table_name,' to \'usr_alan\'@\'%\';')
from   information_schema.tables a
where a.table_schema = 'retailtr_rttlive';

flush privileges;

-- add specific column permissions
grant update (`status`) on retailtr_rttlive.smart_event  to 'usr_alan'@'%';

-- Column permissions must be done this way because TABLE permissions override COL permissions
flush privileges; -- so that revokes find it

revoke select,update,delete on retailtr_rttlive.vendor from 'usr_alan'@'%';
revoke select,update,delete on retailtr_rttlive.ftp_server from 'usr_alan'@'%';
revoke select,update,delete on retailtr_rttlive.principal_vendor from 'usr_alan'@'%';

flush privileges;

-- RUN THIS 2ndary SQL MAMNUALLY !!
select concat('grant select(',column_name,') on retailtr_rttlive.',table_name,' to \'usr_alan\'@\'%\';')
from   information_schema.columns a
where a.table_schema = 'retailtr_rttlive'
and   (
			(lower(a.table_name) = 'vendor' and   lower(column_name) != 'password') or
			(lower(a.table_name) = 'ftp_server' and   lower(column_name) != 'password') or
			(lower(a.table_name) = 'principal_vendor' and   lower(column_name) != 'password')
		);

flush privileges;

-- end column permissions


flush privileges;
