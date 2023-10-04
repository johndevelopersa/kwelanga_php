/*
revoke all on PROCEDURE centura.hierarchy_connect_by_parent_eq_prior_id from centurauser1;
revoke all on PROCEDURE centura.get_hierarchy_for_user from centurauser1;

grant execute on PROCEDURE centura.hierarchy_connect_by_parent_eq_prior_id to centurauser1;
grant execute on PROCEDURE centura.get_hierarchy_for_user to centurauser1;

GRANT all ON mysql.proc TO centurauser1; // can also use String connectionURL = "jdbc:mysql://localhost:3306/mydatabase?user=myuser&password=mypassword&noAccessToProcedureBodies=true"

CREATE USER `centuralogon` IDENTIFIED BY 'no_permissions';
CREATE USER `centuraupduser` IDENTIFIED BY 'Tarzan01_777';
CREATE USER `centuralogon` IDENTIFIED BY 'revelation777';

create user 'rehoboth'@'localhost'  IDENTIFIED BY 'Elijah_01_777';
GRANT ALL PRIVILEGES ON *.*  TO 'rehoboth'@'localhost' with grant option;
create user 'rehoboth'@'%'  IDENTIFIED BY 'Elijah_01_777';
GRANT ALL PRIVILEGES ON *.*  TO 'rehoboth'@'%' with grant option;
*/
create user 'retailtrview'@'%'  IDENTIFIED BY 'rttv1345916472';
GRANT SELECT PRIVILEGES ON *.*  TO 'retailtrview'@'%';

/*
 * LOCALHOSTING setup retailtr user
 */

GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'Gladiator07'; -- this is necessary so that root can access the db remotely !

CREATE USER 'retailtr'@'%' IDENTIFIED BY 'rtt8554';
-- revoke all on *.* from 'retailtr'@'%' identified by 'rtt8554'; 
-- revoke all on *.* from 'retailtr'@'localhost' identified by 'rtt8554'; 
-- revoke all on *.* from 'retailtr'@'127.0.0.1' identified by 'rtt8554';
grant select,insert,update,delete,create,drop on retailtr_rttlive.* to 'retailtr'@'%' identified by 'rtt8554'; -- with grant option; 
grant select,insert,update,delete,create,drop on retailtr_rttlive.* to 'retailtr'@'localhost' identified by 'rtt8554'; -- with grant option; 
grant select,insert,update,delete,create,drop on retailtr_rttlive.* to 'retailtr'@'127.0.0.1' identified by 'rtt8554'; -- with grant option; 

grant execute on FUNCTION alphaNumericValue to 'retailtr'@'%';
grant execute on FUNCTION alphaNumericValue to 'retailtr'@'127.0.0.1';
grant execute on FUNCTION alphaNumericValue to 'retailtr'@'localhost';

grant execute on FUNCTION hasRole to 'retailtr'@'%';
grant execute on FUNCTION hasRole to 'retailtr'@'127.0.0.1';
grant execute on FUNCTION hasRole to 'retailtr'@'localhost';

grant execute on PROCEDURE defineGlobalVars to 'retailtr'@'%';
grant execute on PROCEDURE defineGlobalVars to 'retailtr'@'127.0.0.1';
grant execute on PROCEDURE defineGlobalVars to 'retailtr'@'localhost';

flush privileges;


drop USER 'retailtr'@'%';
drop USER 'retailtr'@'localhost';
drop USER 'retailtr'@'127.0.0.1';
show grants for 'retailtr'@'%'; -- local
show grants for 'retailtr'@'localhost'; -- local
show grants for 'retailtr'@'127.0.0.1'; -- local

select * from mysql.user -- global

