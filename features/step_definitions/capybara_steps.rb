Given /^Habari is not installed$/ do
	empty_db('test')
end

When /^I visit the (.+)$/ do |page_name|
	visit path_to(page_name)
end

When /^I check the database connection$/ do
	click_button "Check Database Connection"
end

When /^I input the (.+) "([^"]*)"$/ do |field, text|
	fill_in field, :with => text
#	page.execute_script("$('##{field}').blur()")
end

Then /^I should see "([^"]+)"$/ do |text|
	page.should have_content(text)
end

Then /^I should not see "([^"]+)"$/ do |text|
	page.should_not have_content(text)
end

Then /^I should see the error message "([^"]+)"$/ do |text|
	page.has_selector?("div.warning", :text => text )
end

Then /^I should not see the error message "([^"]+)"$/ do |text|
	page.has_no_selector?("div.warning", :text => text )
end
