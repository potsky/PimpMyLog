# Asset url tag
# Generate the url for an asset, given the relative path to the asset
# Ex: 'img/my-photo.jpg' -> '/assets/img/my-photo.jpg'
#
# Configure `assets.root_url` in your `config.yml`

module Jekyll
  class AssetUrlTag < Liquid::Tag

    def initialize(tag_name, asset_path, tokens)
      super

      @asset_path = asset_path
    
      @config = Jekyll.configuration({})['assets'] || {}
      @config['root_url']   ||= '/'
    end

    def render(context)
      url_join(@config['root_url'], @asset_path)
    end

    def url_join(s1, s2)
      [strip_trailing_slash(s1), strip_leading_slash(s2)].join('/')
    end

    def strip_leading_slash(text)
      if text[0] == '/'
        text[0] = ''
      end
      return text
    end

    def strip_trailing_slash(text)
      if text[-1] == '/'
        text[-1] = ''
      end
      return text
    end

  end
end

Liquid::Template.register_tag('asset_url', Jekyll::AssetUrlTag)