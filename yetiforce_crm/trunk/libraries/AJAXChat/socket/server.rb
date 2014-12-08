#!/usr/bin/env ruby
  
# Simple Ruby XML Socket Server
#
# This is a a simple socket server implementation in ruby
# to communicate with flash clients via Flash XML Sockets.
# 
# The socket code is based on the tutorial
# "Sockets programming in Ruby"
# by M. Tim Jones (mtj@mtjones.com).
#
# Date:: Tue, 05 Mar 2008
# Author:: Sebastian Tschan, https://blueimp.net
# License:: GNU Affero General Public License

# Include socket library:
require 'socket'
# Include XML libraries:
require 'rexml/document'
require 'rexml/streamlistener'

# XML Stream Handler class used to parse chat messages:
class XMLStreamHandler
  attr_reader :type,:chat_id,:user_id,:reg_id,:channel_id,:channel_ids
  # Called when an opening tag (including attributes) is parsed:
  def tag_start name, attrs
    case name
      when 'root'
        # root messages are broadcast messages:
        @type = :message
        @chat_id = attrs['chatID']
        @channel_id = attrs['channelID']
        throw :break
      when 'register'
        # register messages are sent by chat clients:
        @type = :register
        @chat_id = attrs['chatID']
        @user_id = attrs['userID']
        @reg_id = attrs['regID']
        throw :break
      when 'authenticate'
        # authenticate messages are sent by the chat server client:
        @type = :authenticate
        @chat_id = attrs['chatID']
        @user_id = attrs['userID']
        @reg_id = attrs['regID']
        @channel_ids = Array::new
      when 'channel'
        # authenticate messages contain channel tags:
        if @channel_ids
          @channel_ids.push(attrs['id'])
        else
         throw :break
        end
      when 'policy-file-request'
        # policy-file-requests are sent by flash clients for cross-domain authentication:
        @type = :policy_file_request
        throw :break
      else
        throw :break
    end
  end
  # Called when a closing tag is parsed:
  def tag_end name
    if name == 'authenticate'
      throw :break
    end
  end
  def text text
      # Called on text between tags
  end
  # Called when cdata is parsed:
  alias cdata text
end

# Socket Server class:
class SocketServer

  def initialize(config_file)
    # List of configuration settings:
    @config = Hash::new
    # Initialize default settings:
    initialize_default_properties
    if config_file
      # Load settings from configuration file:
      load_properties_from_file(config_file)
    end
    # Sockets list:
    @sockets = Array::new
    # Clients list:
    @clients = Hash::new
    # Chats list, used to distinguish between different chat installations (contains channels list):
    @chats = Hash::new
    # Initialize server socket:
    initialize_server_socket
    if @server_socket
      # Log server start (STDOUT.flush prevents output buffering):
      puts "#{Time.now}\tServer started on Port #{@config[:server_port].to_s} ..."; STDOUT.flush
      begin
        # Start the server:
        run
      rescue SignalException
        # Controlled stop:
      ensure
        for socket in @sockets
          if socket != @server_socket
            # Disconnect all clients:
            handle_client_disconnection(socket, false)
          end
        end
        @sockets = nil
        @clients = nil
        # Log server stop:
        puts "#{Time.now}\tServer stopped."; STDOUT.flush
      end
    end
  end

  def run
  	# Endless loop:
    while 1
  		# Blocking select call. The first three parameters are arrays of IO objects or nil.
      # The last parameter is to set a timeout in seconds to force select to return
      # if no event has occurred on any of the given IO object arrays.
      res = select(@sockets, nil, nil, nil)
  		if res != nil then
        # Iterate through the tagged read descriptors:
  			for socket in res[0]
          # Received a connect to the server socket:
  				if socket == @server_socket then
  					accept_new_connection
          else
            # Received something on a client socket:
  					if socket.eof? then
              # Handle client disconnection:
              handle_client_disconnection(socket)
  					else
              # Handle client input data:
              handle_client_input(socket, socket.gets(@config[:eol]))
  					end
  				end
  			end
  		end
  	end
  end

  private

  def initialize_default_properties
    # Server address (empty = bind to all available interfaces):
    @config[:server_address] = ''
    # Server port:
    @config[:server_port] = 1935
    # Comma-separated list of clients allowed to broadcast (allows all if empty):
    @config[:broadcast_clients] = ''
    # Defines if broadcast is sent to broadcasting client:
    @config[:broadcast_self] = false
    # Maximum number of clients (0 allows an unlimited number of clients):
    @config[:max_clients] = 0
    # Comma-separated list of domains from which downloaded Flash clients are allowed to connect (* allows all domains):
    @config[:allow_access_from] = '*'
    # Defines the cross-domain-policy string sent to Flash clients as response to a policy-file-request:
    @config[:cross_domain_policy] = '<cross-domain-policy><allow-access-from domain="'+@config[:allow_access_from]+'" to-ports="'+@config[:server_port].to_s+'"/></cross-domain-policy>'
    # EOL (End Of Line) character used by Flash XML Socket communication (a null-byte):
    @config[:eol] = "\0"
    # Log level (0 logs only errors and server start/stop, 1 logs client connections, 2 logs all messages but no broadcast content, 3 logs everything):
    @config[:log_level] = 0
  end

  def load_properties_from_file(config_file)
    # Open the config file and go through each line:
    File.open(config_file, 'r') do |file|
      file.read.each_line do |line|
        # Remove trailing whitespace from the line:
        line.strip!
        # Get the position of the first "=":
        i = line.index('=')
        # Check if line is not a comment and a valid property:
        if (!line.empty? && line[0] != ?# && i > 0)
          # Add the configuration option to the config hash:
          key = line[0..i - 1].strip
          value = line[i + 1..-1].strip
          # Parse boolean values:
          if value.eql?('false')
            @config[key.to_sym] = false
          elsif value.eql?('true')
            @config[key.to_sym] = true
          # Parse integer numbers:
          elsif value.to_i.to_s.eql?(value)
            @config[key.to_sym] = value.to_i
          # Parse floating point numbers:
          elsif value.to_f.to_s.eql?(value)
            @config[key.to_sym] = value.to_f
          # Parse string values:
          else
            @config[key.to_sym] = value
          end
        end
      end      
    end
    if @config[:eol].empty?
      # Use default EOL if configuration option is empty:
      @config[:eol] = $/
    end
  end

  def initialize_server_socket
    begin
      # The server socket, allowing connections from any interface and bound to the given port number:
      @server_socket = TCPServer.new(@config[:server_address], @config[:server_port].to_i)
      # Enable reuse of the server address (e.g. for rapid restarts of the server):
      @server_socket.setsockopt(Socket::SOL_SOCKET, Socket::SO_REUSEADDR, 1)
      # Add the server socket to the sockets list:
      @sockets.push(@server_socket)
    rescue Exception => error
      # Log initialization failure:
      puts "#{Time.now}\tFailed to initialize Server on Port #{@config[:server_port].to_s}: #{error}."; STDOUT.flush
    end
  end

  def accept_new_connection
    begin
      # Accept the client connection (non-blocking):
      socket = @server_socket.accept_nonblock
      # Retrieve IP and Port:
      ip = socket.peeraddr[3]
      port = socket.peeraddr[1]
      # Check if we have reached the maximum number of connected clients (always accept the broadcast clients):
      if @config[:max_clients].to_i == 0 || @clients.size < @config[:max_clients].to_i || !@config[:broadcast_clients].empty? && @config[:broadcast_clients].include?(ip)
        # Add the accepted socket connection to the socket list:
        @sockets.push(socket)
        # Create a new Hash to store the client data:
        client = Hash::new
        client[:id] = "[#{ip}]:#{port}"
        # Check if the client is allowed to broadcast:
        if @config[:broadcast_clients].empty? || @config[:broadcast_clients].include?(ip)
          client[:allowed_to_broadcast] = true
        else
          client[:allowed_to_broadcast] = false
        end
        # Add the client to the clients list:       
        @clients[socket] = client
        if @config[:log_level].to_i > 0
          # Log client connection and the number of connected clients:
          puts "#{Time.now}\t#{client[:id]} Connects\t(#{@clients.size} connected)"; STDOUT.flush
        end
      else
        # Close the socket connection:
        socket.close
      end
    rescue
      # Client disconnected before the address information (IP, Port) could be retrieved.
    end
  end

  def handle_client_disconnection(client_socket, delete_socket=true)
    # Retrieve the client ID for the current socket:
    client_id = @clients[client_socket][:id]
    begin
      # Close the socket connection:
      client_socket.close
    rescue
      # Rescue if closing the socket fails
    end
    if delete_socket
      # Remove the socket from the sockets list:
      @sockets.delete(client_socket)
    end
    # Remove the client ID from the clients list:
    @clients.delete(client_socket)
    if @config[:log_level].to_i > 0
      # Log client disconnection and the number of connected clients:
      puts "#{Time.now}\t#{client_id} Disconnects\t(#{@clients.size} connected)"; STDOUT.flush
    end
  end

  def handle_client_input(client_socket, str)
    # Create a new XML stream handler:
    handler = XMLStreamHandler.new
    begin    
      # As soon as the parser has found the relevant information it throws a :break symbol:
      catch :break do
        # Parse the given input string for XML messages:
        REXML::Document.parse_stream(str, handler)
      end
      # The handler stores a type property to define the parsed XML message:
      case handler.type
        when :message
          handle_broadcast_message(client_socket, handler.chat_id, handler.channel_id, str)
        when :register
          handle_client_registration(client_socket, handler.chat_id, handler.user_id, handler.reg_id)
        when :authenticate
          handle_client_authentication(client_socket, handler.chat_id, handler.user_id, handler.reg_id, handler.channel_ids)
        when :policy_file_request
          handle_policy_file_request(client_socket)
      end    
    rescue Exception => error
      # Rescue if parsing the client input fails and log the error message:
      puts "#{Time.now}\t#{@clients[client_socket][:id]} Client Input Error:#{error.to_s.dump}"; STDOUT.flush
    end
  end
  
  def handle_broadcast_message(client_socket, chat_id, channel_id, str)
    # Check if the_client is allowed to broadcast:
    if @clients[client_socket][:allowed_to_broadcast]
      # Check if the chat and channel have been registered:
      if @chats[chat_id] && (@chats[chat_id][channel_id] || @chats[chat_id]['ALL'])
        # Go through the sockets list:
        @sockets.each do |socket|
          # Skip the server socket and skip the the client socket if broadcast is not to be sent to self:
          if socket != @server_socket && (@config[:broadcast_self] || socket != client_socket)
            # Only write to clients registered to the given channel or to the "ALL" channel:
            if @chats[chat_id]['ALL']
              reg_id = @chats[chat_id]['ALL'][@clients[socket][:user_id]]
            end
            if !reg_id && @chats[chat_id][channel_id]
              reg_id = @chats[chat_id][channel_id][@clients[socket][:user_id]]
            end
            # Check if the reg_id stored for the given channel and user_id matches the clients reg_id:
            if reg_id && reg_id.eql?(@clients[socket][:reg_id])
              begin
                # Write the broadcast message on the socket connection:
                socket.write(str)
              rescue
                # Rescue if writing to the socket fails
              end
            end
          end
        end
      end
      if @config[:log_level].to_i > 2
        # Log the message sent by the broadcast client:
        puts "#{Time.now}\t#{@clients[client_socket][:id]} Chat:#{chat_id.to_s.dump} Channel:#{channel_id.to_s.dump} Message:#{str.to_s.dump}"; STDOUT.flush
      elsif @config[:log_level].to_i > 1
        # Log the message sent by the broadcast client:
        puts "#{Time.now}\t#{@clients[client_socket][:id]} Chat:#{chat_id.to_s.dump} Channel:#{channel_id.to_s.dump} Message"; STDOUT.flush
      end
    end
  end
  
  def handle_client_registration(client_socket, chat_id, user_id, reg_id)
    # Save the chat_id, use_id and reg_id as client properties:
    @clients[client_socket][:chat_id] = chat_id
    @clients[client_socket][:user_id] = user_id
    @clients[client_socket][:reg_id] = reg_id
    if @config[:log_level].to_i > 1
      # Log the client registration:
      puts "#{Time.now}\t#{@clients[client_socket][:id]} Chat:#{chat_id.to_s.dump} User:#{user_id.to_s.dump} Reg:#{reg_id.to_s.dump}"; STDOUT.flush
    end
  end
  
  def handle_client_authentication(client_socket, chat_id, user_id, reg_id, channel_ids)
    # Only the broadcast clients may send authentication messages:
    if @clients[client_socket][:allowed_to_broadcast]
      # Create a new chat item if not found for the given chat_id:
      if !@chats[chat_id]
        @chats[chat_id] = Hash.new
      end
      # Go through the list of channels for the given chat:
      @chats[chat_id].each_key do |key|
        # Delete all items for the given user on all channels of the given chat:
        @chats[chat_id][key].delete(user_id)
        # If the chat channel is empty, delete the channel item:
        if @chats[chat_id][key].size == 0
          @chats[chat_id].delete(key)
        end
      end
      # Go through the list of authenticated channel_ids:
      channel_ids.each do |channel_id|
        # Create a new channel item if not found for the current channel_id (and the given chat_id):
        if !@chats[chat_id][channel_id]
          @chats[chat_id][channel_id] = Hash.new
        end
        # Add a user item of the given user_id with the given reg_id to the current channel:
        @chats[chat_id][channel_id][user_id] = reg_id
      end
      if @config[:log_level].to_i > 1
        # Log the client authentication:
        puts "#{Time.now}\t#{@clients[client_socket][:id]} Chat:#{chat_id.to_s.dump} User:#{user_id.to_s.dump} Auth:#{reg_id.to_s.dump} Channels:#{channel_ids.join(',').dump}"; STDOUT.flush
      end
    end
  end
  
  def handle_policy_file_request(client_socket)
    begin
      # Write the cross-domain-policy to the Flash client:
      client_socket.write(@config[:cross_domain_policy]+@config[:eol])
    rescue
      # Rescue if writing to the socket fails
    end
    if @config[:log_level].to_i > 1
      # Log the policy-file-request:
      puts "#{Time.now}\t#{@clients[client_socket][:id]} Policy-File-Request"; STDOUT.flush
    end
  end

end

# Start the socket server with the first command line argument as configuration file:
SocketServer.new($*[0])