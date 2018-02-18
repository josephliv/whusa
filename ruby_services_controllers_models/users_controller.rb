class UsersController < AuthenticatedController
  set_tab :users

  before_action :set_user, only: [:show, :edit, :update, :destroy]

  def index
    @users = User.order('name ASC')
                 .includes(:group)
                 .page(params[:page])
  end

  def new
    @user = User.new
  end

  def create
    @user = User.new(users_params)
    @user.save

    respond_with @user, location: -> { users_path }
  end

  def edit
    @user.build_address unless @user.address
  end

  def show
  end

  def update
    if params[:user][:password].blank?
      params[:user].delete("password")
      params[:user].delete("password_confirmation")
    end

    @user.update(users_params)

    respond_with @user, location: -> { users_path }
  end

  def destroy
    @user.destroy

    respond_with @user, location: -> { users_path }
  end

  private

  def set_user
    @user = User.find(params[:id])
  end

  def users_params
    params.require(:user).permit(
      :name, :email, :password, :password_confirmation, :hour_price,
      :analysis_price, :client_id, :group_id, :phone,
      address_attributes: [:id, :state, :city, :street, :zipcode],
      bank_accounts_attributes: [
        :id, :_destroy, :name, :number, :country, :agency, :cpf, :routing
      ]
    )
  end

  def validate_permission!
    authorize User
  end
end
