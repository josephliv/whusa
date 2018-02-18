class AuthenticatedController < ApplicationController
  before_action :authenticate_user!
  before_action :validate_permission!

  private

  def validate_permission!
  end
end
