module NavigationHelpers

  def path_to(page_name)
    path = ""

    case page_name

      when /home/
        path = '/'

      when /installer/
        path = '/'

      when /admin/
        path = '/admin'

      when /login/
        path = '/auth/login'

      when /logout/
        path = '/auth/logout'

      when /"(.+)" admin page/
        path = "/admin/#{$1}"

    else
      raise "No map found for \"#{page_name}\" in #{__FILE__}"
    end

    return get_server()+path
  end
end

World(NavigationHelpers)
