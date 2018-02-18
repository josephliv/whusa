module Transactions
  class Create
    attr_reader :project, :user, :member, :project_member

    def initialize(user, project, member)
      @project = project
      @user    = user
      @member  = member
      @project_member = member.members.find_by(project_id: project.id)
    end

    def create(params = {})
      tasks = load_tasks(params.fetch(:task_ids))

      Transaction.transaction do
        transaction = create_transaction(tasks, params)

        tasks.each do |task|
          create_item(transaction, task)
        end

        update_all_tasks(tasks)
      end

      true
    rescue
      false
    end

    private

    def load_tasks(ids)
      tasks = @project.tasks.where(id: ids).where(paid_at: nil)
      fail 'Ids are required' if tasks.blank?
      tasks
    end

    def create_transaction(tasks, params = {})
      hours  = tasks.sum(&:hours)
      amount = tasks.sum(&:brl_amount)

      project.transactions.create!(
        user: member,
        creator: user,
        description: params[:description],
        kind: :project,
        operation: :expense,
        hours: hours,
        hours_price: (amount / hours).round(2),
        amount: amount,
        paid: true
      )
    end

    def create_item(transaction, task)
      price =
        if task.development?
          project_member.hour_price
        else
          project_member.analysis_price
        end

      transaction.items.create!(
        payable: task,
        hours: task.hours,
        price: price,
        amount: task.brl_amount
      )
    end

    def update_all_tasks(tasks)
      tasks.update_all(paid_at: Time.current)
    end
  end
end
