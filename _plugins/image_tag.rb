# Image Tag
#
# Easily put an image into a Jekyll page or blog post with a class
#
# Usage :
# {% image url class="img-responsive" title="" alink="" aclass="" atarget="" %}
#
# Respect parameter order but you can remove some :
# - class
# - title
# - alink
# - aclass
# - atarget
#
# Let link="" to link to the image url
# Let atarget="" to open in a new window named pmlexternal
#
# Examples:
#   Input:
#     {% image url class="img-responsive" title="My image" link="" aclass="aclass" atarget="_self" %}
#   Output:
#     <a href="url" class="aclass" target="_self"><img src="url" class="img-responsive" title="My image"/></a>
#
#   Input:
#     {% image url title="oh"%}
#   Output:
#        <img src="url" title="oh" />
#
module Jekyll
  class ImageTag < Liquid::Tag
    @url            = nil
    @class          = nil
    @title          = nil
    @aclass         = nil
    @atarget        = nil
    IMAGE_URL_CLASS = /(\S+)((\s+)class="([^"]*)")*((\s+)title="([^"]*)")*((\s+)alink="([^"]*)")*((\s+)aclass="([^"]*)")*((\s+)atarget="([^"]*)")*/i

    def initialize(tag_name, markup, tokens)
      super

      if markup =~ IMAGE_URL_CLASS
        @url     = $1
        @class   = $4
        @title   = $7
        @alink   = $10
        @aclass  = $13
        @atarget = $16
      end

      if @alink == ""
        @alink = @url
      end
      if @atarget == ""
        @atarget = "pmlexternal"
      end

    end

    def render(context)

      source = ""
      if @alink
        source += "<a href=\"#{@alink}\""
        source += @aclass ? " class=\"#{@aclass}\"" : ""
        if @atarget
          source += " target=\"#{@atarget}\""
        end
        source += ">"
      end
      source += "<img src=\"#{@url}\""
      source += @class ? " class=\"#{@class}\"" : ""
      source += @title ? " title=\"#{@title}\"" : ""
      source += "/>"
      source += @alink ? "</a>" : ""

      source
    end
  end
end

Liquid::Template.register_tag('image', Jekyll::ImageTag)
