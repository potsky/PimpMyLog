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
#     {% image url class="img-responsive" title="My image" alink="toto" aclass="aclass" atarget="_self" %}
#   Output:
#     <a href="toto" class="aclass" target="_self"><img src="url" class="img-responsive" title="My image"/></a>
#
#   Input:
#     {% image url title="oh"%}
#   Output:
#        <img src="url" title="oh" />
#

# {% image /assets/theypimplogs/videospot.png class="theypimplogs" title="VideoSpot" alink="http://www.videospot.com" aclass="" atarget="theypimplogs" %}

# {% retinabwcolor /assets/theypimplogs/codesnippets.png title="Code Snippets" alink="http://codesnippets.pl" %}



module Jekyll
  class RetinabwcolorTag < Liquid::Tag
    @url            = nil
    @title          = nil
    @alink          = nil
    IMAGE_URL_CLASS = /(\S+)((\s+)title="([^"]*)")*((\s+)alink="([^"]*)")*/i

    def initialize(tag_name, markup, tokens)
      super

      if markup =~ IMAGE_URL_CLASS
        @url     = $1
        @title   = $4
        @alink   = $7
      end

      if @alink == ""
        @alink = @url
      end

    end

    def render(context)

      source = ""
      if @alink
        source += "<a href=\"#{@alink}\">"
      end

      filepath   = File.dirname( __FILE__ ) + '/..' + @url
      dimensions = IO.read( filepath )[0x10..0x18].unpack('NN')
      width      = dimensions[0]
      height     = dimensions[1]

      source += "<img src=\"#{@url}\" class=\"theypimplogs tplr\" width=\"#{width}\" height=\"#{height}\""
      source += @title ? " title=\"#{@title}\"" : ""
      source += "/>"
      source += @alink ? "</a>" : ""

      source
    end
  end
end

Liquid::Template.register_tag('retinabwcolor', Jekyll::RetinabwcolorTag)
