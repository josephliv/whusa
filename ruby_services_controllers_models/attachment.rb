class Attachment < ApplicationRecord
  mount_uploader :file, AttachmentUploader

  belongs_to :user
  belongs_to :attachable, polymorphic: true, optional: true

  validates :file, presence: true
  after_create :update_advert

  delegate :name, to: :user, prefix: true, allow_nil: true

  def filename
    File.basename(file.to_s)
  end

  def audio?
    file.content_type.start_with? 'audio'
  end

  private

  def update_advert
    return unless attachable_type == 'Advert'
    return if attachable.audio? || !audio?

    attachable.update(audio: true)
  end
end
