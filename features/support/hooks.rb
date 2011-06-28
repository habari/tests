def empty_db(database_name)
	sql = "DROP DATABASE IF EXISTS #{database_name}" 
	st = $my.prepare(sql)
	st.execute
	st.close
	sql = "CREATE DATABASE IF NOT EXISTS #{database_name}" 
	st = $my.prepare(sql)
	st.execute
	st.close
end
