context('Login', () => {
  afterEach(() => {
    cy.logout()
  })

  it('Logs in successfully', () => {
    cy.login({ allow_password_auth: true })
    cy.visit('/logout', {
      failOnStatusCode: false
    })
    cy.visit('/login')

    cy.getCy('email').clear().type('user@example.com')
    cy.getCy('password').clear().type('password')
    cy.getCy('form').submit()
    cy.url().should('include', 'home')
  })
})
