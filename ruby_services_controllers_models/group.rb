class Group < ApplicationRecord
  validates :name, :kind, presence: true

  enum kind: {
    regular: 0,
    admin: 1,
    research: 2,
    programmer: 3,
    qa: 4,
    client: 5
  }

  has_many :users

  [:regular, :admin, :research].each do |method_name|
    define_method "#{method_name}?" do
      kind == method_name.to_s
    end
  end

  def can?(permission)
    public_send(permission.to_sym)
  end
end
