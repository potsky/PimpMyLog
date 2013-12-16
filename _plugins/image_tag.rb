# Image Tag
#
# Inspired by:
# https://github.com/stewart/blog/blob/master/plugins/image_tag.rb
#
# Allow retina and lazy loading, using:
# https://github.com/scottjehl/picturefill
# https://github.com/sebarmeli/JAIL

module Jekyll
  class ImageTag < Liquid::Tag
    @url = nil
    @title = nil
    @caption = nil

    IMAGE_URL_WITH_TITLE_AND_CAPTION = /(\S+)(\s+)"(.*?)"(\s+)"(.*?)"/i
    IMAGE_URL_WITH_TITLE = /(\S+)(\s+)"(.*?)"/i
    IMAGE_URL = /(\S+)/i

    def initialize(tag_name, markup, tokens)
      super

      if markup =~ IMAGE_URL_WITH_TITLE_AND_CAPTION
        @url     = $1
        @title   = $3
        @caption = $5
      elsif markup =~ IMAGE_URL_WITH_TITLE
        @url   = $1
        @title = $3
      elsif markup =~ IMAGE_URL
        @url = $1
        @title = ""
      end

      # Config options
      @config = Jekyll.configuration({})['images'] || {}
      @config['root_url']         ||= ''
      @config['retina']           ||= false
      @config['retina_suffix']    ||= '@2x'
      @config['lazy']             ||= false
      @config['lazy_placeholder'] ||= ''

      # Build url
      # Only works if no trailing slash in `root_url`, and no leading in `url`
      @url = [@config['root_url'], @url].join('/')
      @lazy_placeholder_url = [@config['root_url'], @config['lazy_placeholder']].join('/')
    end

    def render(context)
      source = "<figure>"

      # Retina
      if @config['retina']
        

        if @config['lazy']
          # Retina and lazy
          source += "<span data-picture data-alt=\"#{@title}\" data-class=\"lazy\">"

          source += "<span data-src-lazy=\"#{@url}\" data-src=\"#{@lazy_placeholder_url}\"></span>"
          source += "<span data-src-lazy=\"#{retina_url(@url)}\" data-src=\"#{@lazy_placeholder_url}\" "
          source += "data-media=\"(min-resolution: 192dpi),(-webkit-min-device-pixel-ratio: 2),(min--moz-device-pixel-ratio: 2),(-o-min-device-pixel-ratio: 2/1),(min-device-pixel-ratio: 2),(min-resolution: 2dppx)\">"
          source += "</span>"
        else
          # Retina only
          source += "<span data-picture data-alt=\"#{@title}\">"

          source += "<span data-src=\"#{@url}\"></span>"
          source += "<span data-src=\"#{retina_url(@url)}\" "
          source += "data-media=\"(min-resolution: 192dpi),(-webkit-min-device-pixel-ratio: 2),(min--moz-device-pixel-ratio: 2),(-o-min-device-pixel-ratio: 2/1),(min-device-pixel-ratio: 2),(min-resolution: 2dppx)\">"
          source += "</span>"
        end

        source += "<noscript><img src=\"#{@url}\" alt=\"#{@title}\"></noscript>"
        source += "</span>"

      # Lazy only
      elsif @config['lazy']
        source += "<img class=\"lazy\" data-src=\"#{@url}\" src=\"#{@lazy_placeholder_url}\">"

      # Classic
      else
        source += "<img src=\"#{@url}\" alt=\"#{@title}\">"
      end

      source += "<figcaption>#{@caption}</figcaption>" if @caption
      source += "</figure>"

      source
    end

    def retina_url(normal_url)
      retina_url = normal_url
      # Only works with .jpg and .png
      base = normal_url[0..-5]
      extension = normal_url[-3..-1]
      if extension == '.jpg' || extension == '.png'
        retina_url = base + @config['retina_suffix'] + extension
      end
      retina_url
    end
  end
end

Liquid::Template.register_tag('image', Jekyll::ImageTag)
