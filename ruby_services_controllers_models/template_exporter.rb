module Projects
  class TemplateExporter
    attr_reader :content, :project, :template

    # == project Fields
    PLACEHOLDERS = {
      # Contact Name
      'C1' => proc do |project|
        project.client.try(:company_name)
      end
    }

    def initialize(project, params = {})
      @project   = project
      @template = SettingEmail.first_or_create

      @content = @template.contract_body.dup
    end

    def export
      replace_content
    end

    private

    def replace_content
      PLACEHOLDERS.each do |key, function|
        tag = "{#{key}}"

        if content[tag]
          replacement_content = function.call(project)
          @content.gsub!(tag, replacement_content.to_s)
        end
      end

      content
    end
  end
end
